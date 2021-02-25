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

use zOmArRD\core\apis\form\ModalForm;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\mysql\AsyncQueue;
use zOmArRD\core\mysql\InsertQuery;
use zOmArRD\core\ranks\GreekRanks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;
use zOmArRD\core\ranks\instances\{MainGroup, Group, User};
use zOmArRD\core\ranks\manager\PermissionsManager;

class RanksCommand extends Command
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
     * RanksCommand constructor.
     * @param GreekRanks $plugin
     */
    public function __construct(GreekRanks $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("ranks");
        $this->description = "Ranks command.";
        $this->usageMessage = "/ranks help";
        $this->setPermission("ranks.command");
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

        $helptext = [
            1 => "§7 [1] Commands (1-5):\n- §e/ranks help [1- 5] - Shows this.§7\n- §e/ranks setmaingroup {player} {group} {time} - Sets a player's main group.§7\n- §e/ranks setgroup {1/2} {player} {group} {time} - Sets a player's group.§7\n- §e/ranks settag {1/2/3} {player} {tag} {time} - Sets a user's tag.§7\n- §e/ranks getinfo {player} - Gets a player's info (tags, groups, permissions...)§7",
            2 => "§7 [2] Commands (6-10):\n- §e/ranks setmaingroupperm {group} {permission} - Adds a permission for a main group.§7\n- §e/ranks setgroupperm {group} {permission} - Adds a permission for a group.§7\n- §e/ranks setuserperm {player} {permission} {time} - Adds a player's permission.§7\n- §e/ranks removemaingrouperm {group} {permission} - Removes a permission from a main group.§7\n- §e/ranks removegroupperm {group} {permission} - Removes a permission from a group.§7",
            3 => "§7 [3] Commands (11-15):\n- §e/ranks removeuserperm {player} {permission} - Removes a permission from a user.§7\n- §e/ranks getgroupinfo {group} - Gets a groups's info (formats, permissions...).§7\n- §e/ranks getmaingroupinfo {group} - Gets a main group's info (formats, permissions...).§7\n- §e/ranks groups - Gets all groups.§7\n- §e/ranks maingroups - Gets all main groups.§7",
            4 => "§7 [4] Commands (16-20):\n- §e/ranks removegroup {player} {1/2} - Removes the group from a user.§7\n- §e/ranks removetag {player} {1/2/3} - Removes a user's tag.§7\n- §e/ranks reload - Reloads the database and player permissions.§7\n- §e/ranks profile {player} - Shows the profile for a player.§7",
        ];
        if (!isset($args[0])) {
            $sender->sendMessage($helptext[1]);
            return;
        }
        switch ($args[0]) {
            case "help":
                $num = 1;
                if (isset($args[1])) {
                    $num = intval($args[1]);
                }
                if (isset($helptext[$num])) {
                    $sender->sendMessage($helptext[$num]);
                } else {
                    $sender->sendMessage($helptext[1]);
                }
                return;
            case "profile":
                if (!$sender->hasPermission("ranks.profile.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                $form = new ModalForm(function (Player $player, $data) {
                });
                $player = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]);
                if (!$player) {
                    if (GreekRanks::getPlayer($args[1])) {
                        $player = $args[1];
                    } else {
                        $sender->sendMessage($this->prefix . "§cPlayer not found!");
                        return;
                    }
                }
                $user = new User($player);
                $info = $user->getPlayerInfo();
                $name = $info["ign"];
                $maingroup = new MainGroup($info["maingroup"]);
                $formatmaingroup = $maingroup->getFormat();
                $maingroupname = $maingroup->getName();
                $nick = $info["nick"];
                $group1 = new Group($info["group1"]);
                $group1format = $group1->getFormat();
                $group1name = $group1->getName();
                $group2 = new Group($info["group1"]);
                $group2format = $group2->getFormat();
                $group2name = $group2->getName();
                $tag1 = $info["tag1"];
                $tag2 = $info["tag2"];
                $tag3 = $info["tag3"];
                $form->setContent("Name: $name\nNickname: $nick\nMainGroup: $formatmaingroup §r($maingroupname)\nGroup1: $group1format §r($group1name)\nGroup2: $group2format §r($group2name)\nTag1:$tag1\nTag2:$tag2\nTag3:$tag3");
                $form->setButton1("Ok");
                $sender->sendForm($form);
                break;
            case "removegroup":
            case "remgroup":
                if (!$sender->hasPermission("ranks.removegroup.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks removegroup {player} {1/2}");
                    return;
                }
                $player = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]);
                if (!$player) {
                    if (GreekRanks::getPlayer($args[1])) {
                        $player = $args[1];
                    } else {
                        $sender->sendMessage($this->prefix . "§cPlayer not found!");
                        return;
                    }
                }
                $user = new User($player);
                $user->removeGroup($args[2]);
                $sender->sendMessage($this->prefix . "§2Group number " . $args[2] . " has been removed from " . $user->getNick() . "!");
                break;
            case "removetag":
            case "remtag":
                if (!$sender->hasPermission("ranks.removetag.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks removetag {player} {1/2/3}");
                    return;
                }
                $player = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]);
                if (!$player) {
                    if (GreekRanks::getPlayer($args[1])) {
                        $player = $args[1];
                    } else {
                        $sender->sendMessage($this->prefix . "§cPlayer not found!");
                        return;
                    }
                }
                $user = new User($player);
                $user->removeTag($args[2]);
                $sender->sendMessage($this->prefix . "§2Tag number " . $args[2] . " has been removed from " . $user->getNick() . "!");
                break;
            case "settempmaingroup":
                if (!$sender->hasPermission("ranks.settempmaingroup.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2]) || !isset($args[3])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks settempmaingroup {player} {group} {time}");
                    return;
                }
                $player = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]);
                if (!$player) {
                    if (GreekRanks::getPlayer($args[1])) {
                        $player = $args[1];
                    } else {
                        $sender->sendMessage($this->prefix . "§cPlayer not found!");
                        return;
                    }
                }
                $group = MainGroup::getMainGroupByName($args[2]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cGroup not found!");
                    return;
                }
                $timemultiplier = substr($args[3], -1);
                $amount = rtrim($args[3], $timemultiplier);
                if (!is_numeric($amount)) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks settempmaingroup {player} {group} {time}");
                    return;
                }
                $timeunitmultiplier = ["m" => 60, "h" => 3600, "d" => 86400];
                $time = time();
                $timeunits = ["m" => "Minutes", "h" => "Hours", "d" => "Days"];
                $ending = $time + ($amount * $timeunitmultiplier[$timemultiplier]);
                $rawdata = "$amount " . $timeunits[$timemultiplier];
                array_shift($args);
                array_shift($args);
                $user = new User($player);
                break;
            case "settempgroup":
                if (!$sender->hasPermission("ranks.settempgroup.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                break;
            case "settag":
                if (!$sender->hasPermission("ranks.settag.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                $sender->sendMessage($this->prefix . "§eThis feature is still under development.");
                return;
                break;
            case "setmaingroup":
            case "smg":
                if (!$sender->hasPermission("ranks.setmaingroup.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setmaingroup {player} {group} {time}");
                    return;
                }
                $player = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]);
                if (!$player) {
                    if (GreekRanks::getPlayer($args[1])) {
                        $player = $args[1];
                    } else {
                        $sender->sendMessage($this->prefix . "§cPlayer not found!");
                        return;
                    }
                }

                $group = MainGroup::getMainGroupByName($args[2]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cGroup not found!");
                    return;
                }
                $user = new User($player);
                $groupname = $group->getName();
                if (isset($args[3])) {
                    $playername = $player instanceof Player ? $player->getName() : $player;
                    $time = $this->getTime($args[3]);
                    AsyncQueue::submitQuery(new InsertQuery("INSERT INTO tempranks(ign, type, epoch) VALUES ('{$playername}', 0, '{$time[0]}')"));
                    $sender->sendMessage($this->prefix . "§2Player's main group has been updated successfully! ({$time[2]})");
                    if ($player instanceof Player) {
                        $player->sendMessage($this->prefix . "Your main group has been changed to $groupname. ({$time[2]})");
                    }
                } else {
                    $sender->sendMessage($this->prefix . "§2Player's main group has been updated successfully!");
                    if ($player instanceof Player) {
                        $player->sendMessage($this->prefix . "Your main group has been changed to $groupname.");
                    }
                }
                $user->setMainGroup($group);
                break;
            case "setmaingroupperm":
            case "smgp":
                if (!$sender->hasPermission("ranks.setmaingroupperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setmaingroupperm {group} {permission}");
                    return;
                }
                $group = MainGroup::getMainGroupByName($args[1]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cGroup not found!");
                    return;
                }
                $group->addPermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been added to main group " . $group->getName() . ".");
                foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $pl) {
                    $user = new User($pl);
                    if ($user->getUserMainGroup()->getId() === $group->getId()) {
                        $user->applyPermission($args[2]);
                    }
                }
                break;
            case "setgroup":
            case "sg":
                if (!$sender->hasPermission("ranks.setgroup.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2]) || !isset($args[3])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setgroup {1/2} {player} {group} {time}");
                    return;
                }
                if ($args[1] == "1" || "2") {
                    $player = GreekNetwork::getInstance()->getServer()->getPlayer($args[2]);
                    if (!$player) {
                        if (GreekRanks::getPlayer($args[2])) {
                            $player = $args[2];
                        } else {
                            $sender->sendMessage($this->prefix . "§cPlayer not found!");
                            return;
                        }
                    }
                    $group = Group::getGroupByName($args[3]);
                    if (!$group) {
                        $sender->sendMessage($this->prefix . "§cGroup not found!");
                        return;
                    }
                    $user = new User($player);
                    $groupname = $group->getName();
                    if (isset($args[4])) {
                        $playername = $player instanceof Player ? $player->getName() : $player;
                        $time = $this->getTime($args[4]);
                        AsyncQueue::submitQuery(new InsertQuery("INSERT INTO tempranks(ign, type, epoch) VALUES ('{$playername}', 1, '{$time[0]}')"));
                        $sender->sendMessage($this->prefix . "§2Player's main group has been updated successfully! ({$time[2]})");
                        if ($player instanceof Player) {
                            $player->sendMessage($this->prefix . "Your main group has been changed to $groupname. ({$time[2]})");
                        }
                    } else {
                        $sender->sendMessage($this->prefix . "§2Player's group has been updated successfully!");
                        if ($player instanceof Player) {
                            $player->sendMessage($this->prefix . "Your group number " . $args[1] . " has been changed to $groupname.");
                        }
                    }
                    $user->setGroup($args[1], $group);
                } else {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setgroup {1/2} {player} {group} {time}");
                    return;
                }

                break;
            case "g":
            case "groups":
                if (!$sender->hasPermission("ranks.groups.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                $all = GreekRanks::$database->getAll();
                $groups = $all["groups"];
                $body = "§7Groups:\n";
                foreach ($groups as $g) {
                    $body .= "§e " . $g["name"] . ",";
                }
                $sender->sendMessage($body);
                break;
            case "mg":
            case "maingroups":
                if (!$sender->hasPermission("ranks.maingroups.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                $all = GreekRanks::$database->getAll();
                $groups = $all["main-groups"];
                $body = "§7Main Groups:\n";
                foreach ($groups as $g) {
                    $body .= "§e " . $g["name"] . ",";
                }
                $sender->sendMessage($body);
                break;
            case "setuserperm":
            case "setuperm":
                if (!$sender->hasPermission("ranks.setuserperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setuserperm {user} {permission}");
                    return;
                }
                $user = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]) ? GreekNetwork::getInstance()->getServer()->getPlayer($args[1]) : $args[1];
                $playername = $args[1];
                if ($user instanceof Player) {
                    $userinstance = new User($user);
                    $playername = $user->getName();
                } else {
                    if (GreekRanks::getPlayer($playername)) {
                        $userinstance = new User($user);
                    } else {
                        $sender->sendMessage($this->prefix . "§cUser not found!");
                        return;
                    }
                }
                $userinstance->addPermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been added to user " . $playername . ".");
                break;
            case "reloadperms":
            case "reloadgroups":
            case "reload":
                if (!$sender->hasPermission("ranks.reload.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                GreekRanks::$database = new Config(GreekNetwork::getInstance()->getDataFolder() . "db.yml");
                foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $pl) {
                    $user = new User($pl);
                    $user->startPlayer();
                }
                $sender->sendMessage($this->prefix . "§2Reloaded.");
                break;
            case "removeuserperm":
            case "remuserperm":
                if (!$sender->hasPermission("ranks.removeuserperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks removeuserperm {user} {permission}");
                    return;
                }
                $user = GreekNetwork::getInstance()->getServer()->getPlayer($args[1]) ? GreekNetwork::getInstance()->getServer()->getPlayer($args[1]) : $args[1];
                $playername = $args[1];
                if ($user instanceof Player) {
                    $userinstance = new User($user);
                    $playername = $user->getName();
                } else {
                    if (GreekRanks::getPlayer($playername)) {
                        $userinstance = new User($user);
                    } else {
                        $sender->sendMessage($this->prefix . "§cUser not found!");
                        return;
                    }
                }
                $userinstance->removePermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been removed from user " . $playername . ".");
                break;
            case "removemaingrouperm":
            case "remmgperm":
                if (!$sender->hasPermission("ranks.removemaingrouperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks removemaingrouperm {maingroup} {permission}");
                    return;
                }
                $group = MainGroup::getMainGroupByName($args[1]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cThat main group does not exist!");
                    return;
                }
                $group->removePermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been removed from main group " . $group->getName() . ".");
                foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $pl) {
                    $user = new User($pl);
                    if ($user->getUserMainGroup()->getId() == $group->getId()) {
                        $user->reloadPermissions();
                    }
                }
                break;
            case "setgroupperm":
            case "setgperm":
                if (!$sender->hasPermission("ranks.setgroupperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setgroupperm {group} {permission}");
                    return;
                }
                $group = Group::getGroupByName($args[1]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cThat group does not exist!");
                    return;
                }
                $group->addPermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been added to group " . $group->getName() . ".");
                foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $pl) {
                    $user = new User($pl);
                    if ($user->hasGroup($group)) {
                        $user->applyPermission($args[2]);
                    }
                }
                break;
            case "removegroupperm":
            case "remgperm":
                if (!$sender->hasPermission("ranks.removegroupperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks removegroupperm {group} {permission}");
                    return;
                }
                $group = Group::getGroupByName($args[1]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cThat group does not exist!");
                    return;
                }
                $group->removePermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been removed from group " . $group->getName() . ".");
                foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $pl) {
                    $user = new User($pl);
                    if ($user->hasGroup($group)) {
                        $user->reloadPermissions($args[2]);
                    }
                }
                break;
            case "setmaingroupperm":
            case "setmgperm":
                if (!$sender->hasPermission("ranks.setmaingroupperm.command")) {
                    $sender->sendMessage($this->prefix . "§cYou don't have permission to do that!");
                    return;
                }
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage($this->prefix . "§7Usage: §e/ranks setmaingroupperm {maingroup} {permission}");
                    return;
                }
                $group = MainGroup::getMainGroupByName($args[1]);
                if (!$group) {
                    $sender->sendMessage($this->prefix . "§cThat main group does not exist!");
                    return;
                }
                $group->addPermission($args[2]);
                $sender->sendMessage($this->prefix . "§2Permission " . $args[2] . " has been added to main group " . $group->getName() . ".");
                foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $pl) {
                    $user = new User($pl);
                    if ($user->getUserMainGroup()->getId() == $group->getId()) {
                        $user->applyPermission($args[2]);
                    }
                }
                break;
            default:
                $sender->sendMessage($this->prefix . "§cThis subcommand does not exist or is under development. Use the command /ranks help to see a list of my commands.");
                break;
        }
    }

    /**
     * @param $string
     * @return array
     */
    public function getTime($string): array
    {
        $rawMultiplier = substr($string, -1);
        $rawNumber = (int)substr($string, 0, -1);
        $multipliers = ["s" => 1, "m" => 60, "h" => 60 * 60, "d" => 60 * 60 * 24, "M" => 60 * 60 * 24 * 30, "y" => 60 * 60 * 24 * 365];
        $multipliersString = ["s" => "Second(s)", "m" => "Minute(s)", "h" => "Hour(s)", "d" => "Day(s)", "M" => "Month(s)", "y" => "Year(s)"];
        $currentTime = time();
        $endTime = $currentTime + ($rawNumber * $multipliers[$rawMultiplier]);
        return [$endTime, $currentTime, $rawNumber . " " . $multipliersString[$rawMultiplier]];
    }
}