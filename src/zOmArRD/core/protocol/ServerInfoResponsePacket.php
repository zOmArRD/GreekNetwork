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
use function explode;
use function implode;

class ServerInfoResponsePacket extends GreekProxyPacket
{

    /** @var string */
    private $serverName;
    /** @var bool */
    private $selfInfo;
    /** @var int */
    private $onlinePlayers;
    /** @var int */
    private $maxPlayers;
    /** @var string[] */
    private $playerList;
    /** @var string[] */
    private $serverList;

    public function encodePayload(): void
    {
        PacketHelper::writeString($this, $this->serverName);
        PacketHelper::writeBoolean($this, $this->selfInfo);

        PacketHelper::writeInt($this, $this->onlinePlayers);
        PacketHelper::writeInt($this, $this->maxPlayers);

        PacketHelper::writeArray($this, $this->playerList, static function (GreekProxyPacket $buf, string $playerName) {
            PacketHelper::writeString($buf, $playerName);
        });

        PacketHelper::writeArray($this, $this->serverList, static function (GreekProxyPacket $buf, string $serverName) {
            PacketHelper::writeString($buf, $serverName);
        });
    }

    public function decodePayload(): void
    {
        $this->serverName = PacketHelper::readString($this);
        $this->selfInfo = PacketHelper::readBoolean($this);

        $this->onlinePlayers = PacketHelper::readInt($this);
        $this->maxPlayers = PacketHelper::readInt($this);

        $this->playerList = PacketHelper::readArray($this, static function (GreekProxyPacket $buf) {
            return PacketHelper::readString($buf);
        });

        $this->serverList = PacketHelper::readArray($this, static function (GreekProxyPacket $buf) {
            return PacketHelper::readString($buf);
        });
    }

    /**
     * @param GreekProxyPacketHandler $handler
     * @return bool
     */
    public function handle(GreekProxyPacketHandler $handler): bool
    {
        return $handler->handleServerInfoResponse($this);
    }

    public function getPacketId(): int
    {
        return GreekProxyPackets::SERVER_INFO_RESPONSE_PACKET;
    }

    /**
     * @return bool
     */
    public function isResponse(): bool
    {
        return true;
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName): void
    {
        $this->serverName = $serverName;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param bool $selfInfo
     */
    public function setSelfInfo(bool $selfInfo): void
    {
        $this->selfInfo = $selfInfo;
    }

    /**
     * @return bool
     */
    public function isSelfInfo(): bool
    {
        return $this->selfInfo;
    }

    /**
     * @param int $onlinePlayers
     */
    public function setOnlinePlayers(int $onlinePlayers): void
    {
        $this->onlinePlayers = $onlinePlayers;
    }

    /**
     * @return int
     */
    public function getOnlinePlayers(): int
    {
        return $this->onlinePlayers;
    }

    /**
     * @param int $maxPlayers
     */
    public function setMaxPlayers(int $maxPlayers): void
    {
        $this->maxPlayers = $maxPlayers;
    }

    /**
     * @return int
     */
    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    /**
     * @param string[] $playerList
     */
    public function setPlayerList(array $playerList): void
    {
        $this->playerList = $playerList;
    }

    /**
     * @return string[]
     */
    public function getPlayerList(): array
    {
        return $this->playerList;
    }

    /**
     * @param string[] $serverList
     */
    public function setServerList(array $serverList): void
    {
        $this->serverList = $serverList;
    }

    /**
     * @return string[]
     */
    public function getServerList(): array
    {
        return $this->serverList;
    }
}