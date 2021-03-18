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
namespace zOmArRD\core;

use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat as TE;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\command\GreekCommand;
use zOmArRD\core\config\Settings;
use zOmArRD\core\server\ServerManager;
use zOmArRD\core\utils\PersonaSkinAdapter;

final class GreekNetwork extends PluginBase
{
    const CONFIG_VERSION = 1;

    /** @var GreekNetwork|null */
    public static $instance;

    /** @var array $commands */
    public $commands = [];

    /** @var null */
    private $originalAdaptor = null;


    /**
     * @return GreekNetwork
     */
    public static function getInstance(): GreekNetwork
    {
        return self::$instance;
    }

    public function onLoad(): void
    {
        $logger = $this->getServer()->getLogger();
        $logger->info(Settings::$prefix . TE::GREEN . " loading Database");

        self::$instance = $this;

        Extensions::initConfig();
    }

    public function onEnable(): void
    {
        $logger = $this->getServer()->getLogger();

        $this->originalAdaptor = SkinAdapterSingleton::get();
        SkinAdapterSingleton::set(new PersonaSkinAdapter());

        $this->commands = [
            "greek" => $cmd = new GreekCommand()
        ];

        foreach ($this->commands as $command) {
            $this->getServer()->getCommandMap()->register("greek", $command);
        }

        /** @var  $extensions */
        $extensions = new Extensions();
        $extensions->loadExtensions();

        /** @var  $lobby */
        $lobby = GreekNetwork::getInstance()->getServer()->getDefaultLevel();
        $lobby->setTime(0);
        $lobby->stopTime = true;

        $this->crashed = false;

        $logger->info(Settings::$prefix . TE::GREEN . " System loaded");
    }

    public function onDisable()
    {
        if ($this->originalAdaptor !== null) {
            SkinAdapterSingleton::set($this->originalAdaptor);
        }

        foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $players) {
            $players->sendMessage("§7[§c!§7] §cThere has been an error, you have been moved to our fallback server");
            Extensions::getProxy()->transferPlayer($players, "prelobby");
        }

        sleep(1);
    }
}