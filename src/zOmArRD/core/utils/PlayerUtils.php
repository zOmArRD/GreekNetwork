<?php
declare(strict_types=1);

namespace zOmArRD\core\utils;

use pocketmine\player\Player;
use pocketmine\world\Position;
use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;

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

        /** Player inventory */
        $inventory = $player->getInventory();
        $inventory->clearAll();

        $armor = $player->getArmorInventory();
        $armor->clearAll();

    }

    /**
     * @param Player $player
     */
    public static function onPlayerSpawn(Player $player): void
    {
        /** Player SafeSpawn */
        $x = Settings::$x;
        $y = Settings::$y;
        $z = Settings::$z;
        $level = Settings::$lobby;
        $player->teleport(new Position($x, $y, $z, GreekNetwork::getInstance()->getServer()->getWorldManager()->getWorldByName($level)));
    }
}