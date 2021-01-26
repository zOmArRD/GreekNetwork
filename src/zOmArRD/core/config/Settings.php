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
namespace zOmArRD\core\config;

use pocketmine\Server;
use pocketmine\utils\Config;
use zOmArRD\core\GreekNetwork;

/**
 * Class Settings
 * @package zOmArRD\core\config
 */
class Settings
{
    # ================== GENERALS CONFIG ==================

    /** @var string */
    public static $prefix = "";
    public static $fallback_server = "";

    public static $lobby = "";

    public static $joinMessage = "";

    public static $con;
    public static $host;
    public static $username = "";
    public static $password = "";
    public static $database = "";
    public static $server = "";
    public static $config;

    /** @var int */
    public static $x = 50;
    public static $y = 100;
    public static $z = 50;
    public static $gamemode = 2;

    /**
     * @param Config $config
     */
    public final static function init(Config $config): void
    {

        # ================== GENERALS CONFIG ==================
        $general = $config->get("general");
        $mysql = $config->get("Mysql");
        self::$prefix = str_replace("&", "§", $general['prefix']);

        self::$fallback_server = $general["fallback_server"];

        self::$joinMessage = str_replace("&", "§", $general['server_join_message']);
        # ================== GENERALS CONFIG ==================


        # ================== Mysql Config  ==================
        self::$database = $mysql['database'];
        self::$username = $mysql['username'];
        self::$password = $mysql['password'];
        self::$server = $mysql['server'];
        self::$host = $mysql['host'];
        self::$con = new \mysqli(self::$host, self::$username, self::$password, self::$database);

        # ================== Mysql Config  ==================


        # ================== Player Config When Join ==================
        self::$x = $general['x'];
        self::$y = $general['y'];
        self::$z = $general['z'];
        self::$lobby = $general['lobby'];
        self::$gamemode = $general['gamemode'];
        # ================== Player Config When Join ==================

        Server::getInstance()->getLogger()->info(Settings::$prefix . " §aLoaded configuration into system.");
    }
}