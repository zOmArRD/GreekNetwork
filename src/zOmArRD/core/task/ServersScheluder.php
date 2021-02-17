<?php
declare(strict_types=1);

namespace zOmArRD\core\task;

use pocketmine\scheduler\Task;
use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\server\ServerManager;
use zOmArRD\core\utils\Npc;

class ServersScheluder extends Task
{

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        $level = GreekNetwork::getInstance()->getServer()->getLevelByName(Settings::$lobby);
        foreach ($level->getEntities() as $entity) {
            if ($entity instanceof Npc) {
                $tag = "§dLoading resources...";
                switch ($entity->getSkin()->getSkinId()) {
                    case "hcf":
                        $hcf = "§a" . ServerManager::getTotalPlayersByGroup("hcf") . "/200 PLAYERS";
                        $tag = $hcf;
                        break;
                    case "practice":
                        $practice = "§a" . ServerManager::getTotalPlayersByGroup("practice") . "/200 PLAYERS";
                        $tag = $practice;
                        break;
                }
                $entity->setNameTag($tag);
                $entity->setNameTagAlwaysVisible(true);
                $entity->setScale(1);
            }
        }
    }
}