<?php
declare(strict_types=1);

namespace zOmArRD\core\command\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\config\Settings;

class CreateNpcSubCommand implements SubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     * @param string $name
     * @return mixed|void
     */
    public function executeSub(CommandSender $sender, array $args, string $name): void
    {
        if (!$sender instanceof Player) {
            return;
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

        $x = $sender->getX();
        $y = $sender->getY();
        $z = $sender->getZ();
        switch ($args[0]) {
            case "hcf":
            case "h":
                $api->spawnEntity("hcf", $x, $y, $z, $sender->getLevel(), $sender);
                break;
            case "practice":
            case "p":
                $api->spawnEntity("practice", $x, $y, $z, $sender->getLevel(), $sender);
                break;
            case "skywars":
            case "sw":
                $api->spawnEntity("skywars", $x, $y, $z, $sender->getLevel(), $sender);
                break;
            default:
                $sender->sendMessage(Settings::$prefix . "§cerror, npc not found!");
                break;
        }
    }
}