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
namespace zOmArRD\core\addons;

use pocketmine\Player;
use pocketmine\utils\Config;
use zOmArRD\core\apis\text\FloatingTextAPI;
use zOmArRD\core\command\GreekCommand;
use zOmArRD\core\config\Settings;
use zOmArRD\core\events\DataPacketListener;
use zOmArRD\core\events\ItemListener;
use zOmArRD\core\events\PlayerListener;
use zOmArRD\core\events\WorldListener;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\mysql\Mysql;
use zOmArRD\core\task\ServerSyncTask;

/**
 * Class Extensions
 * @package zOmArRD\core\addons
 */
class Extensions
{

    public $commands = [];

    public function loadExtensions(): void
    {
        $this->registerListener();
        $this->registerTask();
        $this->registerCommands();
    }

    function registerListener(): void
    {
        $plugin = GreekNetwork::getInstance()->getServer()->getPluginManager();

        foreach ([new WorldListener(), new PlayerListener(), new DataPacketListener(), new ItemListener()] as $ev) {
            $plugin->registerEvents($ev, GreekNetwork::getInstance());
        }
    }

    function registerTask(): void
    {
        $plugin = GreekNetwork::getInstance()->getScheduler();
       // $plugin->scheduleRepeatingTask(new GreekTask(), 20);
        //$plugin->scheduleRepeatingTask(new ServerSyncTask(), 200);
    }

    function registerCommands(): void
    {
        $this->commands = [
            "greek" => $cmd = new GreekCommand()
        ];
        foreach ($this->commands as $command){
            GreekNetwork::getInstance()->getServer()->getCommandMap()->register("greek", $command);
        }
    }

    public static function initConfig(): void
    {
        $plugin = GreekNetwork::getInstance();

        foreach (['config.yml'] as $strings) $plugin->saveResource($strings);

        $cfg = new Config($plugin->getDataFolder() . "config.yml", Config::YAML);
        if ($cfg->get("config-version") !== GreekNetwork::CONFIG_VERSION) {
            rename($plugin->getDataFolder() . "config.yml", $plugin->getDataFolder() . "config.yml.old");
            $plugin->saveResource("config.yml");
        }

        Settings::init(new Config($plugin->getDataFolder() . "config.yml", Config::YAML));

    }

    /**
     * @param Player $player
     */
    public function sendScoreboard(Player $player): void
    {

    }

    /**
     * @return BungeeExtension
     */
    public static function BungeeCord(): BungeeExtension
    {
        return new BungeeExtension();
    }

    /**
     * @return ScoreExtension
     */
    public static function Scoreboard(): ScoreExtension
    {
        return new ScoreExtension();
    }

    /**
     * @return FloatingTextApi
     */
    public static function FloatingText(): FloatingTextApi
    {
        return new FloatingTextApi();
    }

    /**
     * @return Mysql
     */
    public static function Mysql(): Mysql
    {
        return new Mysql();
    }
}