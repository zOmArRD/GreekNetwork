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
namespace zOmArRD\core\command\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\utils\LanguageManager;

/**
 * Class WorldTeleportSubCommand
 * @package zOmArRD\core\command\subcommand
 */
class WorldTeleportSubCommand implements SubCommand
{

    public function executeSub(CommandSender $sender, array $args, string $name)
    {
        try {
            if (!isset($args[0])) {
                $sender->sendMessage(LanguageManager::getMsg($sender, "world-teleport-usage"));
                return;
            }

            if (!$this->getServer()->isLevelGenerated($args[0])) {
                $sender->sendMessage(LanguageManager::getMsg($sender, "teleport-levelnotexists", [$args[0]]));
                return;
            }

            if (!$this->getServer()->isLevelLoaded($args[0])) {
                $this->getServer()->loadLevel($args[0]);
            }

            $level = $this->getServer()->getLevelByName($args[0]);

            if (!isset($args[1])) {
                if (!$sender instanceof Player) {
                    $sender->sendMessage(Settings::$prefix . LanguageManager::getMsg($sender, "world-teleport-usage"));
                    return;
                }

                $sender->teleport($level->getSafeSpawn());
                $sender->sendMessage(Settings::$prefix . LanguageManager::getMsg($sender, "teleport-done-1", [$level->getName()]));
                return;
            }

            $player = $this->getServer()->getPlayer($args[1]);

            if ((!$player instanceof Player) || !$player->isOnline()) {
                $sender->sendMessage(Settings::$prefix . LanguageManager::getMsg($sender, "teleport-playernotexists"));
                return;
            }

            $player->teleport($level->getSafeSpawn());

            $player->sendMessage(Settings::$prefix . LanguageManager::getMsg($sender, "teleport-done-1", [$level->getName()]));
            $sender->sendMessage(LanguageManager::getMsg($sender, "teleport-done-2", [$level->getName(), $player->getName()]));
            return;
        } catch (\Exception $exception) {
            GreekNetwork::getInstance()->getLogger()->error("An error occurred while teleporting player between worlds: " . $exception->getMessage() . " (at line: " . $exception->getLine() . " , file: " . $exception->getFile() . ")");
        }
    }

    /**
     * @return Server
     */
    private function getServer(): Server
    {
        return Server::getInstance();
    }
}