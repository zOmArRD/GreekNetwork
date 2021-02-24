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
namespace zOmArRD\core\codec;

use zOmArRD\core\protocol\DisconnectPacket;
use zOmArRD\core\protocol\ForwardPacket;
use zOmArRD\core\protocol\HandshakePacket;
use zOmArRD\core\protocol\PingPacket;
use zOmArRD\core\protocol\PongPacket;
use zOmArRD\core\protocol\ReconnectPacket;
use zOmArRD\core\protocol\ServerHandshakePacket;
use zOmArRD\core\protocol\ServerInfoRequestPacket;
use zOmArRD\core\protocol\ServerInfoResponsePacket;
use zOmArRD\core\protocol\ServerTransferPacket;
use zOmArRD\core\protocol\GreekProxyPacket;
use zOmArRD\core\protocol\UnknownPacket;
use pocketmine\utils\Binary;
use function strlen;
use function substr;

class ProtocolCodec
{

    public const STARGATE_MAGIC = 0xa20;

    /** @var GreekProxyPacket[] */
    private $packetPool = [];

    /**
     * ProtocolCodec constructor.
     */
    public function __construct()
    {
        $this->registerPacket(GreekProxyPackets::HANDSHAKE_PACKET, new HandshakePacket());
        $this->registerPacket(GreekProxyPackets::SERVER_HANDSHAKE_PACKET, new ServerHandshakePacket());
        $this->registerPacket(GreekProxyPackets::DISCONNECT_PACKET, new DisconnectPacket());
        $this->registerPacket(GreekProxyPackets::PING_PACKET, new PingPacket());
        $this->registerPacket(GreekProxyPackets::PONG_PACKET, new PongPacket());
        $this->registerPacket(GreekProxyPackets::RECONNECT_PACKET, new ReconnectPacket());
        $this->registerPacket(GreekProxyPackets::FORWARD_PACKET, new ForwardPacket());
        $this->registerPacket(GreekProxyPackets::SERVER_INFO_REQUEST_PACKET, new ServerInfoRequestPacket());
        $this->registerPacket(GreekProxyPackets::SERVER_INFO_RESPONSE_PACKET, new ServerInfoResponsePacket());
        $this->registerPacket(GreekProxyPackets::SERVER_TRANSFER_PACKET, new ServerTransferPacket());
    }

    /**
     * @param int $packetId
     * @param GreekProxyPacket $packet
     * @return bool
     */
    public function registerPacket(int $packetId, GreekProxyPacket $packet): bool
    {
        if (isset($this->packetPool[$packetId])) {
            return false;
        }
        $this->packetPool[$packetId] = clone $packet;
        return true;
    }

    /**
     * @param int $packetId
     * @return GreekProxyPacket|null
     */
    public function getPacketInstance(int $packetId): ?GreekProxyPacket
    {
        if (isset($this->packetPool[$packetId])) {
            return clone $this->packetPool[$packetId];
        }
        return null;
    }

    /**
     * @param int $packetId
     * @return GreekProxyPacket|null
     */
    public function unregisterPacket(int $packetId): ?GreekProxyPacket
    {
        $oldPacket = $this->packetPool[$packetId] ?? null;
        unset($this->packetPool[$packetId]);
        return $oldPacket;
    }

    /**
     * @param GreekProxyPacket $packet
     * @return string
     */
    public function tryEncode(GreekProxyPacket $packet): string
    {
        $encoded = Binary::writeByte($packet->getPacketId());
        $supportsResponse = $packet->isResponse() || $packet->sendsResponse();
        $encoded .= Binary::writeBool($supportsResponse);
        if ($supportsResponse) {
            $encoded .= Binary::writeInt($packet->getResponseId());
        }

        $packet->reset();
        $packet->encodePayload();
        $bodyLength = strlen($packet->getBuffer());

        $encoded .= Binary::writeInt($bodyLength);
        $encoded .= $packet->getBuffer();
        return $encoded;
    }

    /**
     * @param string $encoded
     * @return GreekProxyPacket|null
     */
    public function tryDecode(string $encoded): ?GreekProxyPacket
    {
        $packetId = Binary::readByte($encoded);
        $offset = 1;

        $packet = $this->getPacketInstance($packetId);
        if ($packet === null) {
            $packet = new UnknownPacket();
            $packet->setPacketId($packetId);
        }

        if (Binary::readBool($encoded[$offset++])) {
            $packet->setResponseId(Binary::readInt(substr($encoded, $offset, 4)));
            $offset += 4;
        }

        $bodyLength = Binary::readInt(substr($encoded, $offset, 4));
        $packet->setBuffer(substr($encoded, $offset + 4, $bodyLength));
        $packet->decodePayload();
        return $packet;
    }
}