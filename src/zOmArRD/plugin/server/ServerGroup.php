<?php
declare(strict_types=1);


namespace zOmArRD\plugin\server;

/**
 * Class ServerGroup
 * @package zOmArRD\plugin\server
 */
class ServerGroup
{
    /** @var Server[] $servers */
    public $servers = [];

    /** @var string $name */
    public $name = "";

    /**
     * ServerGroup constructor.
     * @param $name
     * @param $servers
     */
    public function __construct($name, $servers)
    {
        $this->name = $name;
    }

    /**
     * @return Server[]
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * @param Server $server
     * @return bool
     */
    public function addServer(Server $server): bool
    {
        if (strpos($server->getName(), $this->getName()) !== false) {
            $this->servers[] = $server;
            return true;
        }
        return false;
    }

    public function findOptimalServer(/*Player $player*/)
    {
        $servers = $this->getServers();
        $sort = array_map(function (Server $server) {
            return $server->getOnlinePlayers();
        }, $servers);
        asort($sort);
        $finalServer = null;
        foreach ($sort as $key) {
            $server = $servers[$key];
            // TODO: Actually make it join the most optimal server.
        }
        return $finalServer;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}