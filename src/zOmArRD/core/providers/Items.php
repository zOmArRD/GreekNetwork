<?php
declare(strict_types=1);

namespace zOmArRD\core\providers;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

abstract class Items
{
    /**
     * @return Item
     */
    public static function getServerSelectItem(): Item
    {
        return ItemFactory::get(ItemIds::COMPASS)->setCustomName("§r§6§lServer Selector §r§7(use)");
    }
}