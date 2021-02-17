<?php
declare(strict_types=1);

namespace zOmArRD\core\addons;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use zOmArRD\core\providers\Entity as API;
use zOmArRD\core\utils\Npc;

class EntityExtension implements API
{
    public function spawnEntity(string $name, float $x, float $y, float $z, Level $level, Player $player): void
    {
        foreach ($level->getEntities() as $entity){
            if ($entity instanceof Npc){
                if ($entity->getSkin()->getSkinId() === $name){
                    $entity->kill();
                }
            }
        }
        $nbt = Entity::createBaseNBT(new Vector3($x, $y, $z));
        $nbt->setTag(clone $player->namedtag->getCompoundTag("Skin"));
        $human = new Npc($player->getLevel(), $nbt);
        $human->setNameTag("§dloading resources...");
        $human->setNameTagVisible(true);
        $human->setNameTagAlwaysVisible(true);
        $human->setSkin(new Skin($name, $player->getSkin()->getSkinData(), $player->getSkin()->getCapeData(), $player->getSkin()->getGeometryName(), $player->getSkin()->getGeometryData()));
        $human->yaw = $player->getYaw();
        $human->setScale(1);
        $human->pitch = $player->getPitch();
        $human->spawnToAll();
    }
}