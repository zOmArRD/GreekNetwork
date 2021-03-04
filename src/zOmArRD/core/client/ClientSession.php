<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: zOmArRD
 * Date: 18/12/20
 *       ___               _         ____  ____
 *  ____/ _ \ _ __ ___    / \   _ __|  _ \|  _ \
 * |_  / | | | '_ ` _ \  / _ \ | '__| |_) | | | |
 *  / /| |_| | | | | | |/ ___ \| |  |  _ <| |_| |
 * /___|\___/|_| |_| |_/_/   \_\_|  |_| \_\____/
 *
 * Adapted from the Wizardry License
 *
 * Copyright (c) 2020 zOmArRD and contributors
 *
 * Permission is hereby granted to any persons and/or organizations
 * using this software to copy, modify, merge, publish, and distribute it.
 * Said persons and/or organizations are not allowed to use the software or
 * any derivatives of the work for commercial use or any other means to generate
 * income, nor are they allowed to claim this software as their own.
 *
 * The persons and/or organizations are also disallowed from sub-licensing
 * and/or trademarking this software without explicit permission from zOmArRD.
 *
 * Any persons and/or organizations using this software must disclose their
 * source code and have it publicly available, include this license,
 * provide sufficient credit to the original authors of the project (IE: zOmArRD),
 * as well as provide a link to the original project.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR
 * PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace zOmArRD\core\client;

use zOmArRD\core\codec\GreekProxyPacketHandler;
use zOmArRD\core\handler\HandshakePacketHandler;
use zOmArRD\core\protocol\DisconnectPacket;
use zOmArRD\core\protocol\HandshakePacket;
use zOmArRD\core\protocol\PingPacket;
use zOmArRD\core\protocol\PongPacket;
use zOmArRD\core\protocol\GreekProxyPacket;
use zOmArRD\core\protocol\types\PingEntry;
use zOmArRD\core\utils\PacketResponse;
use zOmArRD\core\utils\GreekProxyException;
use zOmArRD\core\utils\GreekProxyFuture;
use Exception;
use pocketmine\plugin\PluginLogger;
use function get_class;
use function microtime;

class ClientSession
{

    /** @var GreekProxyClient */
    private $client;
    /** @var GreekProxyConnection */
    private $connection;

    /** @var int */
    private $responseCounter = 0;
    /** @var PacketResponse[] */
    private $pendingResponses = [];

    /** @var GreekProxyPacketHandler|null */
    private $packetHandler;

    /** @var PingEntry|null */
    private $pingEntry;

    /** @var int */
    private $logInputLevel = 0;
    /** @var int */
    private $logOutputLevel = 0;

    /**
     * ClientSession constructor.
     * @param GreekProxyClient $client
     * @param string $address
     * @param int $port
     */
    public function __construct(GreekProxyClient $client, string $address, int $port)
    {
        $this->client = $client;
        $server = $client->getServer();
        $this->packetHandler = new HandshakePacketHandler($this);
        $this->connection = new GreekProxyConnection($server->getLogger(), $address, $port, $client->getHandshakeData());
    }

    public function onConnect(): void
    {
        $packet = new HandshakePacket();
        $packet->setHandshakeData($this->client->getHandshakeData());
        $this->sendPacket($packet);

        $this->connection->setState(GreekProxyConnection::STATE_AUTHENTICATING);
        $this->client->onSessionConnected();
    }

    public function onTick(): void
    {
        if (!$this->isConnected()) {
            return;
        }

        if ($this->connection->getState() === GreekProxyConnection::STATE_CONNECTED) {
            $this->onConnect();
        }

        while (($payload = $this->connection->inputRead()) !== null && !empty($payload)) {
            $codec = $this->client->getProtocolCodec();
            try {
                $packet = $codec->tryDecode($payload);
                if ($packet !== null) {
                    $this->onPacket($packet);
                }
            } catch (Exception $e) {
                $this->getLogger()->error("§cCan not decode GreekProxy packet!");
                $this->getLogger()->logException($e);
            }
        }


        $currentTime = microtime(true) * 1000;
        if ($this->pingEntry !== null && $currentTime >= $this->pingEntry->getTimeout()) {
            $this->pingEntry->getFuture()->completeExceptionally(new GreekProxyException("Ping Timeout!"));
            $this->pingEntry = null;
        }
    }

    /**
     * @param GreekProxyPacket $packet
     */
    private function onPacket(GreekProxyPacket $packet): void
    {
        $handled = $this->packetHandler !== null && $packet->handle($this->packetHandler);

        if ($packet->isResponse() && isset($this->pendingResponses[$packet->getResponseId()])) {
            $response = $this->pendingResponses[$packet->getResponseId()];
            $response->complete($packet);
            unset($this->pendingResponses[$packet->getResponseId()]);
            $handled = true;
        }

        $customHandler = $this->client->getCustomHandler();
        if ($customHandler !== null) {
            try {
                if ($packet->handle($customHandler)) {
                    $handled = true;
                }
            } catch (Exception $e) {
                $this->getLogger()->error("Error occurred in custom packet handler!");
                $this->getLogger()->logException($e);
            }
        }

        if (!$handled) {
            $this->getLogger()->debug("Unhandled packet " . get_class($packet));
        }

        if ($this->logInputLevel >= $packet->getLogLevel()) {
            $this->getLogger()->debug("Received " . get_class($packet));
        }
    }

    /**
     * @param GreekProxyPacket $packet
     * @return PacketResponse|null
     */
    public function responsePacket(GreekProxyPacket $packet): ?PacketResponse
    {
        if (!$packet->sendsResponse()) {
            return null;
        }

        $responseId = $this->responseCounter++;
        $packet->setResponseId($responseId);
        $this->sendPacket($packet);

        if (!isset($this->pendingResponses[$responseId])) {
            $this->pendingResponses[$responseId] = new PacketResponse();
        }
        return $this->pendingResponses[$responseId];
    }

    /**
     * @param GreekProxyPacket $packet
     */
    public function sendPacket(GreekProxyPacket $packet): void
    {
        if (!$this->isConnected()) {
            return;
        }

        $codec = $this->client->getProtocolCodec();
        try {
            $payload = $codec->tryEncode($packet);
            if (!empty($payload)) {
                $this->connection->writeBuffer($payload);
            }
        } catch (Exception $e) {
            $this->getLogger()->error("§cCan not encode GreekProxy packet " . get_class($packet) . "!");
            $this->getLogger()->logException($e);
            return;
        }

        if ($this->logInputLevel >= $packet->getLogLevel()) {
            $this->getLogger()->debug("Sent " . get_class($packet));
        }
    }

    /**
     * @param int $timeout
     * @return GreekProxyFuture
     */
    public function pingServer(int $timeout): GreekProxyFuture
    {
        if ($this->pingEntry !== null) {
            $this->pingEntry->getFuture();
        }

        $now = (int)microtime(true) * 1000;
        $entry = new PingEntry(new GreekProxyFuture(), $now + $timeout);

        $packet = new PingPacket();
        $packet->setPingTime($now);
        $this->sendPacket($packet);
        return ($this->pingEntry = $entry)->getFuture();
    }

    /**
     * @param PongPacket $packet
     */
    public function onPongReceive(PongPacket $packet): void
    {
        if ($this->pingEntry === null) {
            return;
        }
        $packet->setPongTime((int)microtime(true) * 1000);
        $this->pingEntry->getFuture()->complete($packet);
        $this->pingEntry = null;
    }

    /**
     * @param string $reason
     */
    public function onDisconnect(string $reason): void
    {
        $this->getLogger()->info("§bGreekProxy server has been disconnected! Reason: " . $reason);
        $this->client->onSessionDisconnected();
        $this->close();
    }

    /**
     * @param string $reason
     * @param bool $send
     */
    public function reconnect(string $reason, bool $send): void
    {
        if ($send) {
            $packet = new DisconnectPacket();
            $packet->setReason($reason);
            $this->sendPacket($packet);
        }
        $this->getLogger()->info("§bReconnecting to server! Reason: " . $reason);
        $this->close();
        $this->client->connect();
    }

    /**
     * @param string $reason
     */
    public function disconnect(string $reason): void
    {
        if ($this->connection->isClosed()) {
            return;
        }
        $this->getLogger()->info("§bClosing StarGate connection! Reason: " . $reason);

        $packet = new DisconnectPacket();
        $packet->setReason($reason);
        $this->sendPacket($packet);
        $this->close();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if ($this->connection->isClosed()) {
            return false;
        }

        $this->connection->close();
        return true;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return !$this->connection->isClosed();
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->connection->getState() === GreekProxyConnection::STATE_AUTHENTICATED;
    }

    /**
     * @return GreekProxyPacketHandler|null
     */
    public function getPacketHandler(): ?GreekProxyPacketHandler
    {
        return $this->packetHandler;
    }

    /**
     * @param GreekProxyPacketHandler|null $packetHandler
     */
    public function setPacketHandler(?GreekProxyPacketHandler $packetHandler): void
    {
        $this->packetHandler = $packetHandler;
    }

    /**
     * @return GreekProxyClient
     */
    public function getClient(): GreekProxyClient
    {
        return $this->client;
    }

    /**
     * @return PluginLogger
     */
    public function getLogger(): PluginLogger
    {
        return $this->client->getLogger();
    }

    /**
     * @return GreekProxyConnection
     */
    public function getConnection(): GreekProxyConnection
    {
        return $this->connection;
    }

    /**
     * @param int $logInputLevel
     */
    public function setLogInputLevel(int $logInputLevel): void
    {
        $this->logInputLevel = $logInputLevel;
    }

    /**
     * @return int
     */
    public function getLogInputLevel(): int
    {
        return $this->logInputLevel;
    }

    /**
     * @param int $logOutputLevel
     */
    public function setLogOutputLevel(int $logOutputLevel): void
    {
        $this->logOutputLevel = $logOutputLevel;
    }

    /**
     * @return int
     */
    public function getLogOutputLevel(): int
    {
        return $this->logOutputLevel;
    }
}