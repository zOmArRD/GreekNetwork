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
}