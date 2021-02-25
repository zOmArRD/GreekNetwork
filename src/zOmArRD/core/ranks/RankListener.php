<?php
namespace zOmArRD\core\ranks;

//use EndGames\EndGamesModerator\EndGamesModerator;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\ranks\instances\User;
use pocketmine\event\player\PlayerPreLoginEvent;

class RankListener implements Listener
{
    public $plugin;

    public function __construct(GreekNetwork $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $ev)
    {
        $player = $ev->getPlayer();
        $user = new User($player);
        if (!$user->getPlayerInfo()) {
            $user->init();
        }
        $user->startPlayer();
    }

    public function onPreJoin(PlayerPreLoginEvent $ev)
    {
        $player = $ev->getPlayer();
        $user = new User($player);
        if (!$user->getPlayerInfo()) {
            $user->init();
        }
    }

    /**
     * @priority Highest
     * @param PlayerChatEvent $ev
     */
    public function onChat(PlayerChatEvent $ev)
    {
        /*$moderator = $this->plugin->getServer()->getPluginManager()->getPlugin("EndGamesModerator");
        if ($moderator) {
            if ($moderator->disguised->get($ev->getPlayer()->getName())) {
                return;
            }
        }*/
        if ($ev->isCancelled()) return;
        $player = $ev->getPlayer();
        $msg = $ev->getMessage();
        if (isset(GreekRanks::$chat[$player->getName()])) $ev->setFormat(User::replaceVars(GreekRanks::$chat[$player->getName()], ["{{msg}}" => $msg]));
    }
}