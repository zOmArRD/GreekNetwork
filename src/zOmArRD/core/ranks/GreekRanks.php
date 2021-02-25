<?php
declare(strict_types=1);

namespace zOmArRD\core\ranks;

use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\ranks\tasks\CheckExpiredRanks;
use zOmArRD\core\ranks\commands\{RanksCommand, TagCommand, NickCommand};
use zOmArRD\core\ranks\manager\PermissionsManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;

class GreekRanks
{
    public static $database;
    public static $users;
    public static $config;
    public $att = [];
    public static $PermissionsManager;
    public static $chat = [];

    private $plugin;

    public function __construct(GreekNetwork $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onEnable()
    {
        $this->plugin->getServer()->getPluginManager()->registerEvents(new RankListener($this->plugin), $this->plugin);
        $this->plugin->saveResource("db.yml");
        $this->plugin->saveResource("config.yml");
        self::$database = new Config($this->plugin->getDataFolder() . "db.yml", Config::YAML);
        self::$config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        $commandMap = $this->plugin->getServer()->getCommandMap();
        $commandMap->register("EndGamesRanks", new RanksCommand($this));
        $commandMap->register("EndGamesRanks", new TagCommand($this));
        $commandMap->register("EndGamesRanks", new NickCommand($this));
        $this->plugin->getScheduler()->scheduleRepeatingTask(new CheckExpiredRanks($this->plugin), 100);
        self::$PermissionsManager = new PermissionsManager($this);
        $result = Settings::$con->query("CREATE TABLE IF NOT EXISTS ranks(ign VARCHAR(50) UNIQUE, maingroup INT DEFAULT 0, group1 INT, group2 INT, tag1 TEXT, tag2 TEXT, tag3 TEXT, permissions TEXT, nick TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci");
        Settings::$con->query("CREATE TABLE IF NOT EXISTS tempranks(ign VARCHAR(50), type INT(11), epoch VARCHAR(50)) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci");

        if (!$result) {
            die('There was an error running the query [' . Settings::$con->error . ']');
        }
        $this->plugin->getScheduler()->scheduleRepeatingTask(new CheckExpiredRanks($this->plugin), 100);
        if (self::$config->get("convertdata") == true) {
            $this->plugin->getLogger()->warning("STARTING TO CONVERT DATA FROM YML TO MYSQL.");
            $startTime = microtime(true);
            $database = new Config($this->plugin->getDataFolder() . "users.yml", Config::YAML);
            $keys = array_keys($database->getAll());
            foreach ($keys as $player) {
                $obj = $database->get($player);
                $nick = isset($obj["nick"]) ? $obj["nick"] : $player;
                $permissions = implode(",", $obj["permissions"]);
                $maingroup = $obj["main-group"];
                $group1 = isset($obj["groups"][0]) ? $obj["groups"][0] : 10;
                $group2 = isset($obj["groups"][1]) ? $obj["groups"][1] : 10;
                $tag1 = isset($obj["tags"][0]) ? '' . $obj["tags"][0] : '';
                $tag2 = isset($obj["tags"][1]) ? '' . $obj["tags"][1] : '';
                $tag3 = isset($obj["tags"][2]) ? '' . $obj["tags"][2] : '';
                $res = Settings::$con->query("INSERT INTO ranks(ign, maingroup, group1, group2, tag1,tag2,tag3, permissions, nick) VALUES ('$player', $maingroup, $group1, $group2, '$tag1', '$tag2', '$tag3','$permissions', '$nick')");
                if (!$res) {
                    die('There was an error running the query [' . Settings::$con->error . ']');
                }
            }
            Settings::$con->query("UPDATE ranks SET group1=NULL, group2=NULL WHERE group1=10 AND group2=10");
            $this->plugin->getLogger()->warning("ALL DATA HAS BEEN CONVERTED SUCCESSFULLTY IN " . (microtime(true) - $startTime) . " SECONDS. PLEASE SET convertdata TO false IN THE CONFIG AS IT'S NO LONGER NECESSARY TO COMVERT ANYTHING.");
        }
    }

    /**
     * @param string $player
     * @return bool
     */
    public static function getPlayer(string $player): bool
    {
        $result = Settings::$con->query("SELECT * FROM ranks WHERE ign='$player'");
        return $result->num_rows === 1;
    }
}