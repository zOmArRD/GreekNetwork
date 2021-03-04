<?php
declare(strict_types=1);

namespace zOmArRD\core\command\subcommand;

use pocketmine\command\CommandSender;

interface SubCommand
{
    /**
     * @param CommandSender $sender
     * @param array $args
     * @param string $name
     * @return mixed
     */
    public function executeSub(CommandSender $sender, array $args, string $name): void;
}