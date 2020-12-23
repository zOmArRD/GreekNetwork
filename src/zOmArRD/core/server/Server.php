<?php
declare(strict_types=1);

namespace zOmArRD\core\server;

use EndGames\EndGamesDatabase\AsyncQueue;
use EndGames\EndGamesDatabase\SelectQuery;
use pocketmine\Player;

/**
 * Class Server
 * @package core\manager\servers
 */
class Server
{
    /** @var string $name */
    private $name = "";

    /** @var int $onlinePlayers */
    private $onlinePlayers = 0;

    /** @var bool $online */
    private $online = false;

    /**
     * Server constructor.
     * @param $name
     * @param int $onlinePlayers
     * @param bool $online
     */
    public function __construct($name, $onlinePlayers = 0, $online = false)
    {
        $this->name = $name;
        $this->onlinePlayers = $onlinePlayers;
        $this->onlinePlayers = $online;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOnlinePlayers(): int
    {
        return $this->onlinePlayers;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function sync(): void
    {
        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM servers WHERE server='{$this->name}'"), function ($rows) {
            $row = $rows[0];
            $this->online = $row["status"] == "1";
            $this->onlinePlayers = $row["players"];
        });
    }
}