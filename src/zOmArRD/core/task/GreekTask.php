<?php
declare(strict_types=1);

namespace zOmArRD\core\task;

use pocketmine\scheduler\Task;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\utils\PlayerUtils;

class GreekTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $player) {
            PlayerUtils::sendSC($player);
        }
    }
}