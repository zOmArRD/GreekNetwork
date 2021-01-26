<?php
declare(strict_types=1);

namespace zOmArRD\core\apis\npcForm;

use pocketmine\plugin\Plugin;

class  NpcDialog
{
    /** @var bool */
    static private $registered = false;

    /**
     * @param Plugin $plugin
     */
    static public function register(Plugin $plugin): void
    {
        if (!self::$registered) {
            $plugin->getServer()->getPluginManager()->registerEvents(new PacketListener(), $plugin);
            self::$registered = true;
        }
    }
}