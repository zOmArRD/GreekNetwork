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
namespace zOmArRD\core\ranks\commands;

use zOmArRD\core\apis\form\CustomForm;
use zOmArRD\core\apis\form\SimpleForm;
use zOmArRD\core\ranks\GreekRanks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use zOmArRD\core\ranks\instances\User;
use zOmArRD\core\ranks\manager\PermissionsManager;


/**
 * Class NickCommand
 * @package zOmArRD\core\ranks\commands
 */
class NickCommand extends Command
{
    /** @var GreekRanks */
    protected $plugin;

    /** @var string */
    protected $description;

    /** @var string */
    protected $usageMessage;

    protected $config;
    protected $prefix;

    /** @var PermissionsManager */
    protected $permmanager;

    /**
     * NickCommand constructor.
     * @param GreekRanks $plugin
     */
    public function __construct(GreekRanks $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("nick");
        $this->description = "Nick command.";
        $this->usageMessage = "/nick";
        $this->setPermission("ranks.nick.command");
        $this->prefix = GreekRanks::$config->get("prefix");
        $this->permmanager = new PermissionsManager($plugin);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return;

        if (!$sender->hasPermission("ranks.nick.command")) {
            $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
            return;
        }
        $user = new User($sender);
        $form = new SimpleForm(function (Player $player, $data) {
            if (isset($data)) {
                $user = new User($player);
                if ($data === 1) {
                    $customnicknameform = new CustomForm(function (Player $player, $data) {
                        if ($data) {
                            $nick = $data[1];
                            $maxlength = GreekRanks::$config->get("maximum_nick_length") !== false ? intval(GreekRanks::$config->get("maximum_nick_length")) : 15;
                            if (strlen($nick) > $maxlength) {
                                $player->sendMessage($this->prefix . "§cThat nickname is too long!");
                                return;
                            } else {
                                $user = new User($player);
                                $user->setNick($nick);
                                $player->sendMessage($this->prefix . "§2Your nickname has been set to " . $nick . "!");
                                return;
                            }
                        }
                    });
                    $customnicknameform->setTitle("Nickname Manager");
                    $customnicknameform->addLabel("Choose your new nickname:");
                    $customnicknameform->addInput("Nickname:", "", $user->getNick());
                    $customnicknameform->sendToPlayer($player);
                } else {
                    $user->resetNick();
                    $player->sendMessage($this->prefix . "Your nickname has been reset!");
                }
            }
        });
        $form->setTitle("Nickname Manager");
        $form->setContent("Your current nickname is: " . $user->getNick());
        $form->addButton("§c§lReset Nickname", 0, "textures/ui/cancel.png");
        $form->addButton("§2§lEdit Nickname", 0, "textures/ui/confirm.png");
        $sender->sendForm($form);
    }
}
