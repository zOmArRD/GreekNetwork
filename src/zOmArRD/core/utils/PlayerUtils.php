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
namespace zOmArRD\core\utils;

use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TE;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\providers\Items;

class PlayerUtils
{

    /**
     * @param Player $player
     */
    public static function onPJE(Player $player): void
    {
        /** Player Attributes */
        $player->setMaxHealth(1);
        $player->setHealth(1);
        $player->setFood(20);
        $player->setGamemode(Player::ADVENTURE);

        /** Player inventory */
        $inventory = $player->getInventory();
        $inventory->clearAll();

        $armor = $player->getArmorInventory();
        $armor->clearAll();

    }

    /**
     * @param Player $player
     */
    public static function sendSC(Player $player): void
    {
        $api = Extensions::Scoreboard();
        $api->new($player, $player->getName(), "§l§6Greek §8Network");
        $api->setLine($player, 8, TE::RED . "§7──────────────");
        $api->setLine($player, 7, TE::RED . "§6Nick:");
        $api->setLine($player, 6, TE::GREEN . " §f" . $player->getName());
        $api->setLine($player, 5, TE::AQUA . "§a§e");
        $api->setLine($player, 4, TE::RED . "§6Rank:");
        $api->setLine($player, 3, TE::RED . " §aDefault");
        $api->setLine($player, 2, TE::GREEN . "§a§e");
        $api->setLine($player, 1, TE::GREEN . "§6play.greekmc.net");
        $api->setLine($player, 0, TE::BLACK . "§4§7──────────────");
        $api->getObjectiveName($player);
    }

    /**
     * @param Player $player
     */
    public static function giveItems(Player $player): void
    {
        $inventory = $player->getInventory();
        $inventory->setItem(4, Items::getServerSelectItem());
    }

    /* public function floatingText01(Player $player): void
     {
         $pn = $player->getName();
         $floating = Extensions::FloatingText();

         if ($player instanceof Player) {
             $text1 = $floating->createText(new Vector3(0.50, 89.7, 4.50));
             $text2 = $floating->createText(new Vector3(0.50, 89.3, 4.50));
             $text3 = $floating->createText(new Vector3(0.50, 88.98, 4.50));
             $text4 = $floating->createText(new Vector3(0.50, 88.65, 4.50));
             $text5 = $floating->createText(new Vector3(0.50, 88.35, 4.50));
             $text6 = $floating->createText(new Vector3(0.50, 88.05, 4.50));
             $text7 = $floating->createText(new Vector3(0.50, 87.75, 4.50));
             $text8 = $floating->createText(new Vector3(0.50, 87.45, 4.50));

             $floating->sendText($text1, $player, "§l§6Greek §8Network");
             $floating->sendText($text2, $player, "§7───────────────────");
             $floating->sendText($text3, $player, "§fWelcome§6 $pn");
             $floating->sendText($text4, $player, "§7» §9§lDiscord §r§7«");
             $floating->sendText($text5, $player, "§o§fdiscord.gg/Greek");
             $floating->sendText($text6, $player, "§7» §a§lStore §r§7«");
             $floating->sendText($text7, $player, "§o§fgreekmc.net/shop");
             $floating->sendText($text8, $player, "§7───────────────────");
         }
     } */

    public static function sendFloatingText02(Player $player): void
    {
        $pn = $player->getName();
        $floating = Extensions::FloatingText();

        if ($player instanceof Player) {
            $text1 = $floating->createText(new Vector3(0.50, 64.5, 6.50));
            $text2 = $floating->createText(new Vector3(0.50, 64, 6.50));
            $text3 = $floating->createText(new Vector3(0.50,63.80, 6.50));
            $text4 = $floating->createText(new Vector3(0.50, 63.50, 6.50));
            $text5 = $floating->createText(new Vector3(0.50, 63.10, 6.50));
            $text6 = $floating->createText(new Vector3(0.50, 62.80, 6.50));

            $floating->sendText($text1, $player, "§l§6Greek §7| §r§aLobby");
            $floating->sendText($text2, $player, "§fWelcome §e" . $pn . " §rto §l§6Greek §8Network");
            $floating->sendText($text3, $player, "§fYou can purchase a rank on our store at");
            $floating->sendText($text4, $player, "§6https://greekmc.net §ffor a higher priority");
            $floating->sendText($text5, $player, "§fClick on the §6book §fto");
            $floating->sendText($text6, $player, "§fnavigate to our servers.");
        }
    }

    public static function sendFloatingText03(Player $player): void
    {
        $floating = Extensions::FloatingText();

        if ($player instanceof Player) {
            $text1 = $floating->createText(new Vector3(11.50, 61.20, 18.50));
            $text2 = $floating->createText(new Vector3(11.50, 60.50, 18.50));

            $text3 = $floating->createText(new Vector3(9.50, 61.20, 21.50));
            $text4 = $floating->createText(new Vector3(9.50, 60.50, 21.50));


            $text5 = $floating->createText(new Vector3(-8.50, 61.20, 21.50));
            $text6 = $floating->createText(new Vector3(-8.50, 60.50, 21.50));

            $floating->sendText($text1, $player, "§l§6Greek §7| §r§6SkyWars §7(NA)");
            $floating->sendText($text2, $player, "§dtap to join");

            $floating->sendText($text3, $player, "§l§6Greek §7| §r§aHCF §7(NA)");
            $floating->sendText($text4, $player, "§dtap to join");

            $floating->sendText($text5, $player, "§l§6Greek §7| §r§cPractice §7(NA)");
            $floating->sendText($text6, $player, "§dtap to join");
        }
    }
}