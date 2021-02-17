<?php
declare(strict_types=1);

namespace zOmArRD\core\command\subcommand;

use pocketmine\command\CommandSender;

class HelpSubCommand implements SubCommand
{

    public function executeSub(CommandSender $sender, array $args, string $name)
    {
        if (!isset($args[0])){
            $sender->sendMessage($this->getHelpPage($sender, 1));
            return;
        }

        if (!is_numeric($args[0])) {
            $sender->sendMessage($this->getHelpPage($sender, 1));
            return;
        }

        $sender->sendMessage($this->getHelpPage($sender, (int)$args[0]));
    }

    public function getHelpPage(CommandSender $sender, int $page): string
    {
        $title = "§2--- Mostrando la página de ayuda de Greek Network (/greek help <page> ) ---";
        $text = $title;

        switch ($page) {
            default:
                $text .= "\n" . "§2/greek help §fGreek Help Command";
                $text .= "\n" . "§2/greek setnpc §fSpawn EntityExtension";
                break;
        }
        return $text;
    }
}