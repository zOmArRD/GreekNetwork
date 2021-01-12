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
namespace zOmArRD\core\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\utils\CommandException;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use zOmArRD\core\command\subcommand\WorldTeleportSubCommand;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\utils\LanguageManager;

class GreekCommand extends Command implements PluginIdentifiableCommand
{

    /** @var GreekNetwork $plugin */
    public $plugin;

    /** @var array $subcommands */
    public $subcommands = [];

    /**
     * GreekCommand constructor.
     */
    public function __construct()
    {
        parent::__construct("greek", "GreekNetwork Commands", null, ["gn"]);
        $this->plugin = GreekNetwork::getInstance();
        $this->registerSubcommands();
    }

    public function registerSubcommands(): void
    {
        $this->subcommands["worldtp"] = new WorldTeleportSubCommand;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            if ($sender->hasPermission("greek.cmd")) {
                $sender->sendMessage(LanguageManager::getMsg($sender, "default-usage"));
                return;
            }
            $sender->sendMessage(LanguageManager::getMsg($sender, "not-perms"));
            return;
        }

        if ($this->getSubCommand($args[0]) === null) {
            $sender->sendMessage(LanguageManager::getMsg($sender, "default-usage"));
            return;
        }

    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getSubCommand(string $name)
    {
        switch ($name) {
            case "help":
            case "?":
                return "help";
            case "leveltp":
            case "worldtp":
            case "wtp":
                return "worldtp";
        }
        return null;
    }

    /**
     * @param CommandSender $sender
     * @param string $command
     * @return bool
     */
    public function checkPerms(CommandSender $sender, string $command): bool
    {
        if ($sender instanceof Player) {
            if (!$sender->hasPermission("greek.cmd" . $this->getSubcommand($command))) {
                $sender->sendMessage(LanguageManager::getMsg($sender, "not-perms"));
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * @return Plugin|GreekNetwork
     */
    public function getPlugin(): Plugin
    {
        return GreekNetwork::getInstance();
    }
}