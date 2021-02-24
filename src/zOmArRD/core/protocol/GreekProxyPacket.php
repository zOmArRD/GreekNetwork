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
use zOmArRD\core\utils\LogLevel;
use pocketmine\utils\BinaryStream;

abstract class GreekProxyPacket extends BinaryStream
{

    /** @var int */
    private $responseId;

    abstract public function encodePayload(): void;

    abstract public function decodePayload(): void;

    /**
     * @param GreekProxyPacketHandler $handler
     * @return bool
     */
    public function handle(GreekProxyPacketHandler $handler): bool
    {
        return false;
    }

    /**
     * @return int
     */
    abstract public function getPacketId(): int;

    /**
     * @param int $responseId
     */
    public function setResponseId(int $responseId): void
    {
        $this->responseId = $responseId;
    }

    /**
     * @return int
     */
    public function getResponseId(): int
    {
        return $this->responseId;
    }

    /**
     * @return bool
     */
    public function sendsResponse(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isResponse(): bool
    {
        return false;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return LogLevel::LEVEL_FILTERED;
    }
}