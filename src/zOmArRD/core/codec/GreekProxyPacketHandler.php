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
use zOmArRD\core\protocol\UnknownPacket;

abstract class GreekProxyPacketHandler
{

    /**
     * @param HandshakePacket $packet
     * @return bool
     */
    public function handleHandshake(HandshakePacket $packet): bool
    {
        return false;
    }

    /**
     * @param ServerHandshakePacket $packet
     * @return bool
     */
    public function handleServerHandshake(ServerHandshakePacket $packet): bool
    {
        return false;
    }

    /**
     * @param DisconnectPacket $packet
     * @return bool
     */
    public function handleDisconnect(DisconnectPacket $packet): bool
    {
        return false;
    }

    /**
     * @param PingPacket $packet
     * @return bool
     */
    public function handlePing(PingPacket $packet): bool
    {
        return false;
    }

    /**
     * @param PongPacket $packet
     * @return bool
     */
    public function handlePong(PongPacket $packet): bool
    {
        return false;
    }

    /**
     * @param ReconnectPacket $packet
     * @return bool
     */
    public function handleReconnect(ReconnectPacket $packet): bool
    {
        return false;
    }

    /**
     * @param ForwardPacket $packet
     * @return bool
     */
    public function handleForwardPacket(ForwardPacket $packet): bool
    {
        return false;
    }

    /**
     * @param ServerInfoRequestPacket $packet
     * @return bool
     */
    public function handleServerInfoRequest(ServerInfoRequestPacket $packet): bool
    {
        return false;
    }

    /**
     * @param ServerInfoResponsePacket $packet
     * @return bool
     */
    public function handleServerInfoResponse(ServerInfoResponsePacket $packet): bool
    {
        return false;
    }

    /**
     * @param ServerTransferPacket $packet
     * @return bool
     */
    public function handleServerTransfer(ServerTransferPacket $packet): bool
    {
        return false;
    }

    /**
     * @param UnknownPacket $packet
     * @return bool
     */
    public function handleUnknown(UnknownPacket $packet): bool
    {
        return false;
    }
}