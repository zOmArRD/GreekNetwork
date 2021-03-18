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
namespace zOmArRD\core\server;

class PMQuery {
    /**
     * @param string $host Ip/dns address being queried
     * @param int $port Port on the ip being queried
     * @param int $timeout Seconds before socket times out
     *
     * @return string[]|int[]
     * @throws PmQueryException
     */
    public static function query(string $host, int $port, int $timeout = 4) {
        $socket = @fsockopen('udp://'.$host, $port, $errno, $errstr, $timeout);

        if($errno and $socket !== false) {
            fclose($socket);
            throw new PmQueryException($errstr, $errno);
        }elseif($socket === false) {
            throw new PmQueryException($errstr, $errno);
        }

        stream_Set_Timeout($socket, $timeout);
        stream_Set_Blocking($socket, true);

        // hardcoded magic https://github.com/facebookarchive/RakNet/blob/1a169895a900c9fc4841c556e16514182b75faf8/Source/RakPeer.cpp#L135
        $OFFLINE_MESSAGE_DATA_ID = \pack('c*', 0x00, 0xFF, 0xFF, 0x00, 0xFE, 0xFE, 0xFE, 0xFE, 0xFD, 0xFD, 0xFD, 0xFD, 0x12, 0x34, 0x56, 0x78);
        $command = \pack('cQ', 0x01, time()); // DefaultMessageIDTypes::ID_UNCONNECTED_PING + 64bit current time
        $command .= $OFFLINE_MESSAGE_DATA_ID;
        $command .= \pack('Q', 2); // 64bit guid
        $length = \strlen($command);

        if($length !== fwrite($socket, $command, $length)) {
            throw new PmQueryException("Failed to write on socket.", E_WARNING);
        }

        $data = fread($socket, 4096);

        fclose($socket);

        if(empty($data) or $data === false) {
            throw new PmQueryException("Server failed to respond", E_WARNING);
        }
        if(substr($data, 0, 1) !== "\x1C") {
            throw new PmQueryException("First byte is not ID_UNCONNECTED_PONG.", E_WARNING);
        }
        if(substr($data, 17, 16) !== $OFFLINE_MESSAGE_DATA_ID) {
            throw new PmQueryException("Magic bytes do not match.");
        }

        // TODO: What are the 2 bytes after the magic?
        $data = \substr($data, 35);

        // TODO: If server-name contains a ';' it is not escaped, and will break this parsing
        $data = \explode(';', $data);

        return [
            'GameName' => $data[0] ?? null,
            'HostName' => $data[1] ?? null,
            'Protocol' => $data[2] ?? null,
            'Version' => $data[3] ?? null,
            'Players' => $data[4] ?? null,
            'MaxPlayers' => $data[5] ?? null,
            'ServerId' => $data[6] ?? null,
            'Map' => $data[7] ?? null,
            'GameMode' => $data[8] ?? null,
            'NintendoLimited' => $data[9] ?? null,
            'IPv4Port' => $data[10] ?? null,
            'IPv6Port' => $data[11] ?? null,
            'Extra' => $data[12] ?? null, // TODO: What's in this?
        ];
    }
}