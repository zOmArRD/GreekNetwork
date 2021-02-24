<?php
declare(strict_types=1);

namespace zOmArRD\core\events;

use pocketmine\event\entity\EntityDamageByEntityEvent as EDBEE;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent as DPRE;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\utils\Npc;

class EntityListener implements Listener
{
    public function onEDBEE(EDBEE $e): void
    {
        $entity = $e->getEntity();
        $damager = $e->getDamager();

        if ($entity instanceof Npc) {
            if ($damager instanceof Player) {
                switch ($entity->getSkin()->getSkinId()) {
                    case "hcf":
<<<<<<< HEAD
                        break;
                    case "practice":
=======
                        Extensions::BungeeCord()->transferPlayer($damager, "hcf1");
                        break;
                    case "practice":
                        Extensions::BungeeCord()->transferPlayer($damager, "practice1");
>>>>>>> origin/master
                        break;
                }
                $e->setCancelled();
            } else {
                $e->setCancelled();
            }
        }
    }

    public function onDPRE(DPRE $e): void
    {
        $player = $e->getPlayer();

<<<<<<< HEAD
        if ($e->getPacket() instanceof InventoryTransactionPacket) {
=======
        if ($e->getPacket() instanceof InventoryTransactionPacket){
>>>>>>> origin/master
            try {
                $action = $e->getPacket()->trData->actionType == InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT;
            } catch (\ErrorException $e) {
                return;
            }
            if ($action) {
                try {
                    $target = $e->getPlayer()->level->getEntity($e->getPacket()->trData->entityRuntimeId);
                } catch (\ErrorException $e) {
                    return;
                }
<<<<<<< HEAD
                if ($target instanceof Npc) {
                    switch ($target->getSkin()->getSkinId()) {
                        case "hcf":
                            break;
                        case "practice":
=======
                if ($target instanceof Npc){
                    switch ($target->getSkin()->getSkinId()){
                        case "hcf":
                            Extensions::BungeeCord()->transferPlayer($player, "hcf1");
                            break;
                        case "practice":
                            Extensions::BungeeCord()->transferPlayer($player, "practice1");
>>>>>>> origin/master
                            break;
                        default:
                            return;
                    }
                }
            }
        }
    }
}
