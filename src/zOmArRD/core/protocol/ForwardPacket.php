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

namespace zOmArRD\core\protocol;

use zOmArRD\core\codec\GreekProxyPacketHandler;
use zOmArRD\core\codec\GreekProxyPackets;
use zOmArRD\core\protocol\types\PacketHelper;

class ForwardPacket extends GreekProxyPacket
{

    /**
     * @param string $clientName
     * @param GreekProxyPacket $packet
     * @return ForwardPacket
     */
    public static function from(string $clientName, GreekProxyPacket $packet): ForwardPacket
    {
        $forwardPacket = new ForwardPacket();
        $forwardPacket->setClientName($clientName);
        $forwardPacket->setForwardPacketId($packet->getPacketId());

        $packet->reset();
        $packet->encodePayload();
        $forwardPacket->setPayload($packet->getBuffer());
        return $forwardPacket;
    }

    /** @var string */
    private $clientName;
    /** @var int */
    private $forwardPacketId;
    /** @var string */
    private $payload;

    public function encodePayload(): void
    {
        PacketHelper::writeString($this, $this->clientName);
        $this->putByte($this->forwardPacketId);
        PacketHelper::writeByteArray($this, $this->payload);
    }

    public function decodePayload(): void
    {
        $this->clientName = PacketHelper::readString($this);
        $this->forwardPacketId = PacketHelper::readInt($this);
        $this->payload = PacketHelper::readByteArray($this);
    }

    /**
     * @return int
     */
    public function getPacketId(): int
    {
        return GreekProxyPackets::FORWARD_PACKET;
    }

    /**
     * @param GreekProxyPacketHandler $handler
     * @return bool
     */
    public function handle(GreekProxyPacketHandler $handler): bool
    {
        return $handler->handleForwardPacket($this);
    }

    /**
     * @param string $clientName
     */
    public function setClientName(string $clientName): void
    {
        $this->clientName = $clientName;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @param int $forwardPacketId
     */
    public function setForwardPacketId(int $forwardPacketId): void
    {
        $this->forwardPacketId = $forwardPacketId;
    }

    /**
     * @return int
     */
    public function getForwardPacketId(): int
    {
        return $this->forwardPacketId;
    }

    /**
     * @param string $payload
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }
}