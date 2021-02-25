<?php
namespace zOmArRD\core\ranks\tasks;

use zOmArRD\core\GreekNetwork;
use zOmArRD\core\mysql\AsyncQueue;
use zOmArRD\core\mysql\InsertQuery;
use zOmArRD\core\mysql\SelectQuery;
use zOmArRD\core\ranks\GreekRanks;
use zOmArRD\core\ranks\instances\MainGroup;
use zOmArRD\core\ranks\instances\User;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class CheckExpiredRanks extends Task
{
    public $plugin;
    public $prefix;

    public function __construct(GreekNetwork $plugin)
    {
        $this->prefix = GreekRanks::$config->get("prefix");
        $this->plugin = $plugin;
    }

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        $time = time();
        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM tempranks WHERE epoch < {$time}"), function ($res, $data) {
            foreach ($res as $response) {
                $user = new User($response["ign"]);
                $instance = Server::getInstance()->getPlayer($response["ign"]);
                switch ($response["type"]) {
                    case 0:
                        $user->setMainGroup(new MainGroup(GreekRanks::$database->get("default")));
                        break;
                    case 1:
                        $user->removeGroup(1);
                        $user->removeGroup(2);
                        break;
                }
                if ($instance) {
                    $user->reloadPermissions();
                    $user->startPlayer();
                    $instance->sendMessage($this->prefix . "§fYour rank has expired.");
                }
            }
            AsyncQueue::submitQuery(new InsertQuery("DELETE FROM tempranks WHERE epoch < {$data[0]}"));
        }, [$time]);
    }
}