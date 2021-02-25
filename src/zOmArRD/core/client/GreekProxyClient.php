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

use zOmArRD\core\codec\ProtocolCodec;
use zOmArRD\core\events\ClientAuthenticatedEvent;
use zOmArRD\core\events\ClientConnectedEvent;
use zOmArRD\core\events\ClientDisconnectedEvent;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\handler\SessionHandler;
use zOmArRD\core\protocol\DisconnectPacket;
use zOmArRD\core\protocol\GreekProxyPacket;
use zOmArRD\core\protocol\types\HandshakeData;

use zOmArRD\core\utils\PacketResponse;
use pocketmine\plugin\PluginLogger;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class GreekProxyClient extends Task
{

    /** @var GreekNetwork  */
    private $loader;
    /** @var Server */
    private $server;
    /** @var PluginLogger */
    private $logger;

    /** @var ProtocolCodec */
    private $protocolCodec;

    /** @var HandshakeData */
    private $handshakeData;
    /** @var string */
    protected $address;
    /** @var int */
    protected $port;

    /** @var ClientSession|null */
    private $session;

    /** @var SessionHandler|null */
    private $customHandler;

    /**
     * GreekProxyClient constructor.
     * @param string $address
     * @param int $port
     * @param HandshakeData $handshakeData
     * @param GreekNetwork $plugin
     */
    public function __construct(string $address, int $port, HandshakeData $handshakeData, GreekNetwork $plugin)
    {
        $this->loader = $plugin;
        $this->server = $plugin->getServer();
        $this->logger = $plugin->getLogger();
        $this->protocolCodec = new ProtocolCodec();

        $this->address = $address;
        $this->port = $port;
        $this->handshakeData = $handshakeData;
        $this->loader->getScheduler()->scheduleDelayedRepeatingTask($this, 20, $this->loader->getTickInterval());
    }

    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }
        $this->session = new ClientSession($this, $this->address, $this->port);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        if ($this->session !== null && $this->isConnected()) {
            $this->session->onTick();
        }
    }

    public function onSessionConnected(): void
    {
        $this->logger->info("§bClient " . $this->getClientName() . " has connected!");
        $event = new ClientConnectedEvent($this, $this->loader);
        $event->call();

        if ($this->session !== null) {
            $this->session->setLogInputLevel($this->loader->getLogLevel());
            $this->session->setLogOutputLevel($this->loader->getLogLevel());
        }
    }

    public function onSessionAuthenticated(): void
    {
        $event = new ClientAuthenticatedEvent($this, $this->loader);
        $event->call();
        if ($this->session !== null && $event->isCancelled()) {
            $this->session->disconnect($event->getCancelMessage());
        }
    }

    public function onSessionDisconnected(): void
    {
        $event = new ClientDisconnectedEvent($this, $this->loader);
        $event->call();
    }

    /**
     * @param GreekProxyPacket $packet
     */
    public function sendPacket(GreekProxyPacket $packet): void
    {
        if ($this->session !== null) {
            $this->session->sendPacket($packet);
        }
    }

    /**
     * @param GreekProxyPacket $packet
     * @return PacketResponse|null
     */
    public function responsePacket(GreekProxyPacket $packet): ?PacketResponse
    {
        if ($this->session !== null) {
            return $this->session->responsePacket($packet);
        }
        return null;
    }

    public function shutdown(): void
    {
        if (!$this->isConnected()) {
            return;
        }

        if ($this->session !== null) {
            $this->session->disconnect(DisconnectPacket::CLIENT_SHUTDOWN);
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->session !== null && $this->session->isConnected();
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->session !== null && $this->session->isAuthenticated();
    }

    /**
     * @return HandshakeData
     */
    public function getHandshakeData(): HandshakeData
    {
        return $this->handshakeData;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->handshakeData->getClientName();
    }

    /**
     * @return ClientSession|null
     */
    public function getSession(): ?ClientSession
    {
        return $this->session;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @return PluginLogger
     */
    public function getLogger(): PluginLogger
    {
        return $this->logger;
    }

    /**
     * @return ProtocolCodec
     */
    public function getProtocolCodec(): ProtocolCodec
    {
        return $this->protocolCodec;
    }

    /**
     * @return SessionHandler|null
     */
    public function getCustomHandler(): ?SessionHandler
    {
        return $this->customHandler;
    }

    /**
     * @param SessionHandler $customHandler
     */
    public function setCustomHandler(SessionHandler $customHandler): void
    {
        $this->customHandler = $customHandler;
    }
}