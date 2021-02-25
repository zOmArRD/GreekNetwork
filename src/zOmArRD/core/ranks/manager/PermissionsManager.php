<?php
declare(strict_types=1);

namespace zOmArRD\core\ranks\manager;

use zOmArRD\core\GreekNetwork;
use zOmArRD\core\ranks\GreekRanks;
use pocketmine\Player;

class PermissionsManager
{
    public $plugin;

    /**
     * PermissionsManager constructor.
     * @param GreekRanks $plugin
     */
    public function __construct(GreekRanks $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     * @return mixed|\pocketmine\permission\PermissionAttachment
     */
    public function setPerm(Player $player)
    {
        if (!isset($this->plugin->att[$player->getId()])) {
            return $this->plugin->att[$player->getId()] = $player->addAttachment(GreekNetwork::getInstance());
        }
        return $this->plugin->att[$player->getId()];
    }
}