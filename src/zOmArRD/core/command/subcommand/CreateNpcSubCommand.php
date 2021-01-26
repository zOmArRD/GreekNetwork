<?php
declare(strict_types=1);

namespace zOmArRD\core\command\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Server;

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
        $sender->sendMessage("test command");
    }

    /**
     * @return Server $server
     */
    private function getServer(): Server
    {
        return Server::getInstance();
    }
}