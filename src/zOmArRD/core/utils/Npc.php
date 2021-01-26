<?php
declare(strict_types=1);

namespace zOmArRD\core\utils;

use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class Npc extends Human
{
    /**
     * Npc constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
        $this->propertyManager->setFloat(self::DATA_SCALE, 1.00);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "";
    }
}