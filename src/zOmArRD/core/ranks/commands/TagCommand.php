<?php
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
use zOmArRD\core\ranks\instances\{Tag, User};
use zOmArRD\core\ranks\manager\PermissionsManager;

class TagCommand extends Command
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
     * TagCommand constructor.
     * @param GreekRanks $plugin
     */
    public function __construct(GreekRanks $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("tag");
        $this->description = "Tags command.";
        $this->usageMessage = "/tag";
        $this->setPermission("ranks.tag.command");
        $this->prefix = GreekRanks::$config->get("prefix");
        $this->permmanager = new PermissionsManager($plugin);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (!$sender->hasPermission("ranks.tag.command") || !$sender instanceof Player) {
            $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
            return;
        }
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data !== null) {
                $user = new User($player);
                switch ($data) {
                    case 0:
                        $this->openRegularTagForm($player, $user, 1);
                        break;
                    case 1:
                        $this->openRegularTagForm($player, $user, 2);
                        break;
                    case 2:
                        $this->openCustomTagForm($player, $user);
                        break;
                }
            }
        });
        $form->setTitle("Choose the tag you want to edit:");
        if ($sender->hasPermission("ranks.tag1")) $form->addButton("Tag 1");
        if ($sender->hasPermission("ranks.tag2")) $form->addButton("Tag 2");
        if ($sender->hasPermission("ranks.customtag")) $form->addButton("Custom Tag");
        $sender->sendForm($form);
    }

    /**
     * @param Player $player
     * @param User $user
     * @param int $tagNum
     */
    public function openRegularTagForm(Player $player, User $user, int $tagNum)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            $user = new User($player);
            if ($data !== null) {
                $dataApr = explode("-", $data);
                $targetTag = $dataApr[1];
                $tagID = $dataApr[0];
                if ($tagID === "disable") {
                    $message = $this->prefix . "§2Tag disabled.";
                    $user->removeTag($targetTag);
                } else {
                    $tagInfo = GreekRanks::$database->getAll()["tags"][$tagID . ""];
                    if (isset($tagInfo["permission"])) {
                        if ($player->hasPermission($tagInfo["permission"])) {
                            $user->setTag($targetTag, new Tag($tagID));
                            $message = $this->prefix . "§2Tag updated!";
                        } else {
                            $message = $this->prefix . "§cDon't have permission to use that tag.";
                        }
                    } else {
                        $message = $this->prefix . "§2Tag updated.";
                        $user->setTag($targetTag, new Tag($tagID));
                    }
                }
                $player->sendMessage($message);
            }
        });
        $form->addButton("§cDisable", -1, "", "disable-" . $tagNum);
        foreach (GreekRanks::$database->getAll()["tags"] as $tagID => $tag) {
            if (isset($tag["permission"])) {
                if ($player->hasPermission($tag["permission"])) {
                    $form->addButton($tag["format"] . "\n§2Unlocked", -1, "", $tagID . "-" . $tagNum);
                } else {
                    $form->addButton($tag["format"] . "\n§cLocked", -1, "", $tagID . "-" . $tagNum);
                }
            } else {
                $form->addButton($tag["format"] . "\n§2Unlocked", -1, "", $tagID . "-" . $tagNum);
            }
        }
        $form->setTitle("Choose Your Tag $tagNum");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param User $user
     */
    public function openCustomTagForm(Player $player, User $user)
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data !== null && isset($data["customtag"])) {
                $tag3 = $data["customtag"];
                $user = new User($player);
                $maxlength = GreekRanks::$config->get("maximum_custom_tag_length") !== false ? intval(GreekRanks::$config->get("maximum_custom_tag_length")) : 15;
                if (strlen($tag3) > $maxlength) {
                    $message = $this->prefix . "§cYour custom tag is too long!";
                } else {
                    if (strpos($tag3, '}') || strpos($tag3, '{') || strpos($tag3, '(') || strpos($tag3, ')') || strpos($tag3, '[') || strpos($tag3, ']')) {
                        $message = $this->prefix . "§cYour custom tag contains invalid characters!";
                    } else {
                        $message = $this->prefix . "§2Your custom tag has been changed to $tag3";
                        $user->setTag(3, $tag3);
                    }
                }
                $player->sendMessage($message);
            }
        });
        $form->setTitle("Choose New Custom Tag:");
        $form->addInput("Custom Tag:", "", $user->getTag(3) ? $user->getTag(3) : "", "customtag");
        $player->sendForm($form);
    }
}

