<?php

declare(strict_types=1);

namespace zOmArRD\core\task;

use pocketmine\scheduler\Task;
use zOmArRD\core\GreekNetwork;

class MakeAsyncTask extends Task
{

    public function onRun(int $currentTick)
    {
        GreekNetwork::getInstance()->getServer()->getAsyncPool()->submitTask(new NpcAsync());
    }
}