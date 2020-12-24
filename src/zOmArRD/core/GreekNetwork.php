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

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TE;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\config\Settings;
use zOmArRD\core\utils\DiscordWebhook;

final class GreekNetwork extends PluginBase
{
    const CONFIG_VERSION = 1;

    /** @var GreekNetwork|null */
    public static $instance;

    /**
     * @return GreekNetwork|null
     */
    public static function getInstance(): ?GreekNetwork
    {
        return self::$instance;
    }

    public function onLoad()
    {
        $logger = $this->getServer()->getLogger();
        $logger->info(Settings::$prefix . TE::GREEN . " loading Database");

        self::$instance = $this;

        $this->initConfig();

    }

    public function onEnable()
    {
        $logger = $this->getServer()->getLogger();

        $extensions = new Extensions();
        $extensions->loadExtensions();

        $lobby = GreekNetwork::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
        $lobby->setTime(0);
        $lobby->stopTime = true;

        //DiscordWebhook::onEnable();
        $logger->info(Settings::$prefix . TE::GREEN . " System loaded");
    }

    public function initConfig(): void
    {
        $this->saveResource("config.yml");

        $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($cfg->get("config-version") !== GreekNetwork::CONFIG_VERSION) {
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml.old");
            $this->saveResource("config.yml");
        }
        Settings::init(new Config($this->getDataFolder() . "config.yml", Config::YAML));
    }
}