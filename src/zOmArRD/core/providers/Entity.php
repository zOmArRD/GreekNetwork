<?php
declare(strict_types=1);

namespace zOmArRD\core\providers;

use pocketmine\level\Level;
use pocketmine\Player;

interface Entity
{
    /**
     * @param string $name
     * @param float $x
     * @param float $y
     * @param float $z
     * @param Level $level
     * @param Player $player
     * @return mixed
     */
    public function spawnEntity(string $name, float $x, float $y, float $z, Level $level, Player $player): void;
}