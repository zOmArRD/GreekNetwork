<?php
declare(strict_types=1);

namespace zOmArRD\core\utils;

use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
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
}