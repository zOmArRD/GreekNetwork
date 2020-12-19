<?php
namespace zOmArRD\plugin\server;

use EndGames\EndGamesDatabase\AsyncQueue;
use EndGames\EndGamesDatabase\Database;
use EndGames\EndGamesDatabase\SelectQuery;
use zOmArRD\plugin\GreekNetwork;
use zOmArRD\plugin\task\ServerSyncTask;

/**
 * Class ServerManager
 * @package core\manager\servers
 */
class ServerManager
{

    /** @var Server[] $servers */
    public static $servers = [];

    /** @var ServerGroup[] $serverGroups */
    public static $serverGroups = [];

    /** @var Server $currentServer */
    public static $currentServer = null;


    /**
     * @param GreekNetwork $plugin
     * @param array $groups
     */
    public static function init(GreekNetwork $plugin, array $groups)
    {
        $plugin->getScheduler()->scheduleRepeatingTask(new ServerSyncTask(), 60);
        foreach ($groups as $group) {
            self::$serverGroups[$group] = new ServerGroup($group, []);
        }
        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM servers;"), function ($rows) {
            foreach ($rows as $row) {
                $server = new Server($row["server"], $row["players"], $row["status"] === 1);
                if ($row["server"] === Database::getInstance()->getCurrentServerName()) {
                    self::$currentServer = $server;
                } else {
                    self::$servers[] = $server;
                }
                foreach (self::$serverGroups as $serverGroup) {
                    if ($serverGroup->addServer($server)) {
                        break;
                    }
                }
            }

        });

    }

    /**
     * @param $name
     * @return ServerGroup|null
     */
    public static function getGroup($name): ?ServerGroup
    {
        return isset(self::$serverGroups[$name]) ? self::$serverGroups[$name] : null;
    }

    /**
     * @return int
     */
    public static function getTotalPlayers(): int
    {
        $int = count(\pocketmine\Server::getInstance()->getOnlinePlayers());
        foreach (self::$servers as $server) {
            $int = $int + $server->getOnlinePlayers();
        }
        return $int;
    }

    /**
     * @param string $group
     * @return int
     */
    public static function getTotalPlayersByGroup(string $group): int
    {
        $servers = self::getGroup($group);
        $num = 0;

        if ($servers !== null) {
            foreach ($servers->servers as $server) {

                $num = $num + (int)$server->getOnlinePlayers();
            }
        }
        return $num;
    }

    /**
     * @return Server[]
     */
    public static function getServers(): array
    {
        return self::$servers;
    }

    /**
     * @param string $name
     * @return Server|null
     */
    public static function getServerByName(string $name): ?Server
    {
        $serverFinal = null;
        foreach (self::getServers() as $server) {
            if ($server->getName() === $name) {
                $serverFinal = $server;
            }
        }
        return $serverFinal;
    }

    /**
     * @return ServerGroup[]
     */
    public static function getServergroups(): array
    {
        return self::$serverGroups;
    }
}
