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

use Exception;
use RuntimeException;
use function socket_create;
use function socket_last_error;
use function socket_strerror;
use const AF_INET;
use const SOCK_STREAM;
use const SOL_TCP;

class GreekProxySocket
{

    /** @var GreekProxyConnection */
    private $conn;

    /** @var string */
    private $address;
    /** @var int */
    private $port;
    /** @var string */

    /**
     * GreekProxySocket constructor.
     * @param GreekProxyConnection $conn
     * @param string $address
     * @param int $port
     */
    public function __construct(GreekProxyConnection $conn, string $address, int $port)
    {
        $this->conn = $conn;
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        try {
            if ($socket === false) {
                throw new RuntimeException(socket_strerror(socket_last_error()));
            }
            if (!@socket_connect($socket, $this->address, $this->port)) {
                throw new RuntimeException(socket_strerror(socket_last_error()));
            }

            socket_set_nonblock($socket);
            socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
        } catch (Exception $e) {
            $this->conn->getLogger()->error("Can not connect to StarGate server!");
            $this->conn->getLogger()->logException($e);
            return false;
        }

        $this->conn->socket = $socket;
        return true;
    }

    public function close(): void
    {
        socket_close($this->conn->socket);
    }
}