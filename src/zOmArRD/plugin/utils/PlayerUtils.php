<?php
declare(strict_types=1);

namespace zOmArRD\plugin\utils;

use pocketmine\level\Position;
use pocketmine\Player;
use zOmArRD\plugin\config\Settings;
use zOmArRD\plugin\GreekNetwork;

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
        $player->setSaturation(20);

        /** Player inventory */
        $inventory = $player->getInventory();
        $inventory->clearAll();

        $armor = $player->getArmorInventory();
        $armor->clearAll();

    }

    public static function onPlayerSpawn(Player $player): void
    {
        /** Player SafeSpawn */
        $x = Settings::$x;
        $y = Settings::$y;
        $z = Settings::$z;
        $level = Settings::$lobby;
        $player->teleport(new Position(575, 69, 191, GreekNetwork::getInstance()->getServer()->getLevelByName($level)));
    }
}