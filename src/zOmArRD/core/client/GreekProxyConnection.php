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
use zOmArRD\core\protocol\types\HandshakeData;
use zOmArRD\core\utils\GreekProxyException;
use pocketmine\Thread;
use pocketmine\utils\Binary;
use Threaded;
use ThreadedLogger;
use function socket_read;
use function strlen;
use function substr;
use const PHP_BINARY_READ;

class GreekProxyConnection extends Thread
{

    public const STATE_DISCONNECTED = 0;
    public const STATE_CONNECTING = 1;
    public const STATE_CONNECTED = 2;
    public const STATE_AUTHENTICATING = 3;
    public const STATE_AUTHENTICATED = 4;
    public const STATE_SHUTDOWN = 5;

    /** @var ThreadedLogger */
    private $logger;
    /** @var GreekProxySocket */
    private $starGateSocket;

    /** @var resource */
    public $socket;

    /** @var string */
    private $address;
    /** @var int */
    private $port;
    /** @var HandshakeData */
    private $handshakeData;

    /** @var Threaded */
    private $input;
    /** @var Threaded */
    private $output;

    /** @var string */
    private $buffer = "";

    /** @var int */
    private $state = self::STATE_DISCONNECTED;

    /**
     * GreekProxyConnection constructor.
     * @param ThreadedLogger $logger
     * @param string $address
     * @param int $port
     * @param HandshakeData $handshakeData
     */
    public function __construct(ThreadedLogger $logger, string $address, int $port, HandshakeData $handshakeData)
    {
        $this->logger = $logger;
        $this->address = $address;
        $this->port = $port;
        $this->handshakeData = $handshakeData;
        $this->starGateSocket = new GreekProxySocket($this, $this->address, $this->port);

        $this->input = new Threaded();
        $this->output = new Threaded();
        $this->start(PTHREADS_INHERIT_NONE);
    }

    public function run(): void
    {
        $this->registerClassLoader();
        gc_enable();
        error_reporting(-1);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        register_shutdown_function([$this, 'shutdown']);
        //set_error_handler([$this, 'errorHandler'], E_ALL);

        $this->state = self::STATE_CONNECTING;
        $this->logger->debug("Connecting to StarGate server " . $this->address);

        if (!$this->starGateSocket->connect()) {
            $this->state = self::STATE_DISCONNECTED;
            return;
        }
        //socket_getpeername($this->socket, $this->address, $this->port);

        $this->state = self::STATE_CONNECTED;
        $this->operate();
    }

    private function operate(): void
    {
        while ($this->state !== self::STATE_DISCONNECTED) {
            $start = microtime(true);
            $this->onTick();
            $time = microtime(true);
            if ($time - $start < 0.01) {
                time_sleep_until($time + 0.01 - ($time - $start));
            }
        }
        $this->onTick();
        $this->shutdown();
    }

    private function onTick(): void
    {
        $error = socket_last_error();
        socket_clear_error($this->socket);

        if ($error === 10057 || $error === 10054 || $error === 10053) {
            error:
            $this->getLogger()->info("§cConnection with StarGate server has disconnected unexpectedly!");
            $this->close();
            return;
        }

        $data = @socket_read($this->socket, 65536, PHP_BINARY_READ);
        if ($data !== "") {
            $this->buffer .= $data;
        }

        while (($packet = $this->outRead()) !== null && $packet !== "") {
            if (@socket_write($this->socket, $packet) === false) {
                goto error;
            }
        }

        $this->readBuffer();
    }

    /**
     * @param string $buffer
     * @param int $len
     * @param int $offset
     * @return int
     */
    private function verifyHeader(string $buffer, int $len, int $offset): int
    {
        if (($offset + 2) > $len) {
            // No PacketId + Response info
            return 0;
        }

        $index = 1; // PacketID
        $supportsResponse = Binary::readBool($buffer[$offset + $index++]);
        if ($supportsResponse) {
            $index += 4; // Skip ResponseID
        }
        return $index;
    }

    private function readBuffer(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $offset = 0;
        $len = strlen($this->buffer);
        while ($offset < $len) {
            // Packet header consists of 8 bytes
            if ($offset > ($len - 4)) {
                break;
            }

            $magic = Binary::readShort(substr($this->buffer, $offset, 2));
            if ($magic !== ProtocolCodec::STARGATE_MAGIC) {
                throw new GreekProxyException("'Magic does not match!");
            }
            $offset += 2;

            $headerLen = $this->verifyHeader($this->buffer, $len, $offset);
            if ($headerLen < 1 || ($offset + $headerLen + 4) > $len) {
                // Buffer is not complete
                $offset -= 2;
                break;
            }

            $bodyLength = Binary::readInt(substr($this->buffer, $offset + $headerLen, 4));
            $headerLen += 4;

            if (($len - $offset) <= $bodyLength) {
                // We dont have full payload
                // Reset offset and wait for payload
                $offset -= 2;
                break;
            }

            // Header length + Packet payload length
            $payload = substr($this->buffer, $offset, ($payloadLen = $headerLen + $bodyLength));
            $this->inputWrite($payload);
            $offset += $payloadLen;
        }

        if ($offset < $len) {
            $this->buffer = substr($this->buffer, $offset);
        } else {
            $this->buffer = "";
        }
    }

    /**
     * @param string $payload
     */
    public function writeBuffer(string $payload): void
    {
        $buf = Binary::writeShort(ProtocolCodec::STARGATE_MAGIC);
        $buf .= $payload;
        $this->outWrite($buf);
    }

    public function close(): void
    {
        if ($this->state === self::STATE_DISCONNECTED) {
            return;
        }
        $this->state = self::STATE_DISCONNECTED;
        $this->logger->debug("Closed StarGate session " . $this->address);
    }

    public function shutdown(): void
    {
        if ($this->state === self::STATE_SHUTDOWN) {
            return;
        }
        $this->state = self::STATE_SHUTDOWN;
        $this->starGateSocket->close();
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->state === self::STATE_DISCONNECTED || $this->state === self::STATE_SHUTDOWN;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string|null
     */
    public function inputRead(): ?string
    {
        return $this->input->shift();
    }

    /**
     * @param string $string
     */
    public function inputWrite(string $string): void
    {
        $this->input[] = $string;
    }

    /**
     * @return string|null
     */
    public function outRead(): ?string
    {
        return $this->output->shift();
    }

    /**
     * @param string $string
     */
    public function outWrite(string $string): void
    {
        $this->output[] = $string;
    }

    public function quit(): void
    {
        $this->close();
        parent::quit();
    }

    /**
     * @return GreekProxySocket
     */
    public function getStarGateSocket(): GreekProxySocket
    {
        return $this->starGateSocket;
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @return ThreadedLogger
     */
    public function getLogger(): ThreadedLogger
    {
        return $this->logger;
    }

    public function getClientName(): string
    {
        return $this->handshakeData->getClientName();
    }

    public function getThreadName(): string
    {
        return "StarGate-Atlantis";
    }

    public function setGarbage(): void
    {
    }
}