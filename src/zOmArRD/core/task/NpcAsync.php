<?php
declare(strict_types=1);

namespace zOmArRD\core\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use zOmArRD\core\addons\EntityExtension;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\server\Proxy;

class NpcAsync extends AsyncTask
{
    public $hcf, $skywars, $practice;

    public function onRun()
    {
        $this->hcfUpdate();
    }

    public function onCompletion(Server $server)
    {
        EntityExtension::applyNames("hcf", $this->hcf);
    }

    public function hcfUpdate()
    {
        $cl = "§a";
        $players = $this->getProxy()->getServerPlayers("45.134.8.141", 19132);
        $maxPlayer = $this->getProxy()->getServerMaxPlayers("45.134.8.141", 19132);
        if ($players !== -1) {
            $tag = $cl . $players . "/" . $maxPlayer . " PLAYERS";
            $this->hcf = $tag;
        } else {
            $tag = "§cOFFLINE";
            $this->hcf = $tag;
        }
    }

    /**
     * @return Proxy
     */
    public function getProxy(): Proxy
    {
        return Extensions::getProxy();
    }
}