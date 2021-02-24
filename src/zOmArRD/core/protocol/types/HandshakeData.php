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

namespace zOmArRD\core\protocol\types;

use zOmArRD\core\protocol\HandshakePacket;

class HandshakeData
{

    public const SOFTWARE_POCKETMINE = 0;
    public const SOFTWARE_PMMP4 = 1;

    /**
     * @param HandshakePacket $packet
     * @param HandshakeData $handshakeData
     */
    public static function encodeData(HandshakePacket $packet, HandshakeData $handshakeData): void
    {
        PacketHelper::writeInt($packet, $handshakeData->getSoftware());
        PacketHelper::writeString($packet, $handshakeData->getClientName());
        PacketHelper::writeString($packet, $handshakeData->getPassword());
        PacketHelper::writeInt($packet, $handshakeData->getProtocolVersion());
    }

    /**
     * @param HandshakePacket $packet
     * @return HandshakeData
     */
    public static function decodeData(HandshakePacket $packet): HandshakeData
    {
        $software = PacketHelper::readInt($packet);
        $clientName = PacketHelper::readString($packet);
        $password = PacketHelper::readString($packet);
        $protocolVersion = PacketHelper::readInt($packet);
        return new HandshakeData($clientName, $password, $software, $protocolVersion);
    }

    /** @var string */
    private $clientName;
    /** @var string */
    private $password;
    /** @var int */
    private $software;
    /** @var int */
    private $protocolVersion;

    /**
     * HandshakeData constructor.
     * @param string $clientName
     * @param string $password
     * @param int $software
     * @param int $protocolVersion
     */
    public function __construct(string $clientName, string $password, int $software, int $protocolVersion)
    {
        $this->clientName = $clientName;
        $this->password = $password;
        $this->software = $software;
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getSoftware(): int
    {
        return $this->software;
    }

    /**
     * @return int
     */
    public function getProtocolVersion(): int
    {
        return $this->protocolVersion;
    }
}