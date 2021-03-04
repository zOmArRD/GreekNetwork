<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: zOmArRD
 * Date: 18/12/20
 *       ___               _         ____  ____
 *  ____/ _ \ _ __ ___    / \   _ __|  _ \|  _ \
 * |_  / | | | '_ ` _ \  / _ \ | '__| |_) | | | |
 *  / /| |_| | | | | | |/ ___ \| |  |  _ <| |_| |
 * /___|\___/|_| |_| |_/_/   \_\_|  |_| \_\____/
 *
 * Adapted from the Wizardry License
 *
 * Copyright (c) 2020 zOmArRD and contributors
 *
 * Permission is hereby granted to any persons and/or organizations
 * using this software to copy, modify, merge, publish, and distribute it.
 * Said persons and/or organizations are not allowed to use the software or
 * any derivatives of the work for commercial use or any other means to generate
 * income, nor are they allowed to claim this software as their own.
 *
 * The persons and/or organizations are also disallowed from sub-licensing
 * and/or trademarking this software without explicit permission from zOmArRD.
 *
 * Any persons and/or organizations using this software must disclose their
 * source code and have it publicly available, include this license,
 * provide sufficient credit to the original authors of the project (IE: zOmArRD),
 * as well as provide a link to the original project.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR
 * PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace zOmArRD\core;

use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat as TE;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\client\GreekProxyClient;
use zOmArRD\core\command\GreekCommand;
use zOmArRD\core\config\Settings;
use zOmArRD\core\events\ClientCreationEvent;
use zOmArRD\core\protocol\ServerInfoRequestPacket;
use zOmArRD\core\protocol\ServerTransferPacket;
use zOmArRD\core\protocol\types\HandshakeData;
use zOmArRD\core\server\ServerManager;
use zOmArRD\core\utils\PacketResponse;
use zOmArRD\core\utils\PersonaSkinAdapter;

final class GreekNetwork extends PluginBase
{
    const CONFIG_VERSION = 1;

    /** @var GreekNetwork|null */
    public static $instance;

    /** @var array $commands */
    public $commands = [];

    /** @var bool $crashed */
    private $crashed = true;

    /** @var null */
    private $originalAdaptor = null;

    /** @var GreekNetwork[] */
    protected $clients = [];

    /** @var int */
    private $tickInterval;

    /** @var string */
    private $defaultClient;

    /** @var int */
    private $logLevel;

    /** @var bool */
    private $autoStart;

    /**
     * @return GreekNetwork
     */
    public static function getInstance(): GreekNetwork
    {
        return self::$instance;
    }

    public function onLoad(): void
    {
        $logger = $this->getServer()->getLogger();
        $logger->info(Settings::$prefix . TE::GREEN . " loading Database");

        self::$instance = $this;

        Extensions::initConfig();
    }

    public function onEnable(): void
    {
        $logger = $this->getServer()->getLogger();

        $this->originalAdaptor = SkinAdapterSingleton::get();
        SkinAdapterSingleton::set(new PersonaSkinAdapter());

        $this->tickInterval = $this->getConfig()->get("tickInterval");
        $this->defaultClient = $this->getConfig()->get("defaultClient");
        $this->logLevel = $this->getConfig()->get("logLevel");
        $this->autoStart = $this->getConfig()->get("autoStart");

        $this->clients = [];
        foreach ($this->getConfig()->get("connections") as $clientName => $ignore) {
            $this->createClient($clientName);
        }

        $this->commands = [
            "greek" => $cmd = new GreekCommand()
        ];

        foreach ($this->commands as $command) {
            $this->getServer()->getCommandMap()->register("greek", $command);
        }

        /** @var  $extensions */
        $extensions = new Extensions();
        $extensions->loadExtensions();

        /** @var  $mysql */
        $mysql = Extensions::Mysql();
        $mysql->onLoad();

        /** @var  $lobby */
        $lobby = GreekNetwork::getInstance()->getServer()->getDefaultLevel();
        $lobby->setTime(0);
        $lobby->stopTime = true;

        ServerManager::init($this, ["lobby", "hcf", "practice", "ffa"]);

        $this->crashed = false;

        //DiscordWebhook::onEnable();
        $logger->info(Settings::$prefix . TE::GREEN . " System loaded");
    }

    public function onDisable()
    {
        if ($this->originalAdaptor !== null) {
            SkinAdapterSingleton::set($this->originalAdaptor);
        }

        /*foreach (GreekNetwork::getInstance()->getServer()->getOnlinePlayers() as $players) {
            Extensions::BungeeCord()->transferPlayer($players, "hcf1");
        }*/


        foreach ($this->clients as $client) {
            $client->shutdown();
        }

        /** @var  $mysql */
        $mysql = Extensions::Mysql();
        $mysql->onDisable();

        try {
            if ($this->crashed) return;
            $this->getLogger()->info(Settings::$prefix . " §cSystem disabled");
        } catch (\Throwable $error) {
            MainLogger::getLogger()->logException($error);
            $this->getLogger()->info(Settings::$prefix . "The System have problems, contact the Dev");
        }
        sleep(1);
    }

    /**
     * @param string $clientName
     */
    private function createClient(string $clientName): void
    {
        if (!isset($this->getConfig()->get("connections")[$clientName])) {
            $this->getLogger()->warning("§cCan not load client " . $clientName . "! Wrong config!");
            return;
        }

        $config = $this->getConfig()->get("connections")[$clientName];
        $handshakeData = new HandshakeData($clientName, $config["password"], HandshakeData::SOFTWARE_POCKETMINE, self::CONFIG_VERSION);
        $client = new GreekProxyClient($config["address"], (int)$config["port"], $handshakeData, $this);
        $this->onClientCreation($clientName, $client);
    }

    /**
     * @param string $clientName
     * @param GreekProxyClient $client
     */
    public function onClientCreation(string $clientName, GreekProxyClient $client): void
    {
        if (isset($this->clients[$clientName])) {
            return;
        }

        $event = new ClientCreationEvent($client, $this);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }
        if ($this->autoStart) {
            $client->connect();
        }
        $this->clients[$clientName] = $client;
    }

    /**
     * @return int
     */
    public function getTickInterval(): int
    {
        return $this->tickInterval;
    }

    /**
     * @param string $clientName
     * @return GreekProxyClient|null
     */
    public function getClient(string $clientName): ?GreekProxyClient
    {
        return $this->clients[$clientName] ?? null;
    }

    /**
     * @return GreekProxyClient|null
     */
    public function getDefaultClient(): ?GreekProxyClient
    {
        return $this->getClient($this->defaultClient);
    }

    /**
     * @return GreekProxyClient[]
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel(int $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    /**
     * Transfer player to another server.
     * @param Player $player instance to be transferred.
     * @param string $targetServer server where player will be sent.
     * @param string|null $clientName client name that will be used.
     */
    public function transferPlayer(Player $player, string $targetServer, ?string $clientName = null): void
    {
        $client = $this->getClient($clientName ?? $this->defaultClient);
        if ($client === null) {
            return;
        }
        $packet = new ServerTransferPacket();
        $packet->setPlayerName($player->getName());
        $packet->setTargetServer($targetServer);
        $client->sendPacket($packet);
    }

    /**
     * Get info about another server or master server.
     * @param string $serverName name of server that info will be send. In selfMode it can be custom.
     * @param bool $selfMode if send info of master server, StarGate server.
     * @param string|null $clientName client name that will be used.
     * @return PacketResponse|null future that can be used to get response data.
     */
    public function serverInfo(string $serverName, bool $selfMode, ?string $clientName = null): ?PacketResponse
    {
        $client = $this->getClient($clientName ?? $this->defaultClient);
        if ($client === null) {
            return null;
        }
        $packet = new ServerInfoRequestPacket();
        $packet->setServerName($serverName);
        $packet->setSelfInfo($selfMode);
        return $client->responsePacket($packet);
    }
}