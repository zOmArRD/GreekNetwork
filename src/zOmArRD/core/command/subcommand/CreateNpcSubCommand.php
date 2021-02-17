<?php
declare(strict_types=1);

namespace zOmArRD\core\command\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use zOmArRD\core\addons\Extensions;

class CreateNpcSubCommand implements SubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     * @param string $name
     * @return mixed|void
     */
    public function executeSub(CommandSender $sender, array $args, string $name)
    {
        if (!$sender instanceof Player){
            return true;
        }
        if (!isset($args[0])) {
            if ($sender->hasPermission("greek.cmd")) {
                $sender->sendMessage('§cUso: §7/greek setnpc <game>');
                return;
            }
            $sender->sendMessage('§cNo tienes permiso para usar este comando');
            return;
        }

        $api = Extensions::Entity();
        switch ($args[0]) {
            case "hcf":
            case "h":
                $api->spawnEntity("hcf", $sender->getX(), $sender->getY(), $sender->getZ(), $sender->getLevel(), $sender);
                break;
            case "practice":
            case "p":
                $api->spawnEntity("practice", $sender->getX(), $sender->getY(), $sender->getZ(), $sender->getLevel(), $sender);
                break;
            default:
                $sender->sendMessage("§cNpc invalido!");
                return;
        }
        return true;
    }

    /**
     * @return Server $server
     */
    private function getServer(): Server
    {
        return Server::getInstance();
    }
}