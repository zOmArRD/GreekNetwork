<?php
declare(strict_types=1);

namespace zOmArRD\plugin\task;

use pocketmine\scheduler\Task;
use zOmArRD\plugin\server\ServerManager;

class ServerSyncTask extends Task
{

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        foreach (ServerManager::getServers() as $server) {
            $server->sync();
        }
    }
}