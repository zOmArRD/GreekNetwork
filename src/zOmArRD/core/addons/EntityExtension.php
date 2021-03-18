<?php
declare(strict_types=1);

namespace zOmArRD\core\addons;

use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\providers\Entity as API;
use zOmArRD\core\server\Proxy;
use zOmArRD\core\utils\Npc;

class EntityExtension implements API
{
    /**
     * @param string $name
     * @param float $x
     * @param float $y
     * @param float $z
     * @param Level $level
     * @param Player $player
     */
    public function spawnEntity(string $name, float $x, float $y, float $z, Level $level, Player $player): void
    {
        foreach ($level->getEntities() as $entity) {
            if ($entity instanceof Npc) {
                if ($entity->getSkin()->getSkinId() === $name) {
                    $entity->kill();
                }
            }
        }

        $nbt = new CompoundTag("", [
            new ListTag("Pos", [
                new DoubleTag("", $x),
                new DoubleTag("", $y),
                new DoubleTag("", $z)
            ]),
            new ListTag("Motion", [
                new DoubleTag("", 0),
                new DoubleTag("", 0),
                new DoubleTag("", 0)
            ]),
            new ListTag("Rotation", [
                new FloatTag("", $player->yaw),
                new FloatTag("", $player->pitch)
            ]),
            new CompoundTag("Skin", [
                new StringTag("Data", $player->getSkin()->getSkinData()),
                new StringTag("Name", $player->getSkin()->getSkinId()),
            ]),]);
        $human = new Npc($player->getLevel(), $nbt);
        $human->setScale(1);
        $human->setSkin(new Skin($name, $player->getSkin()->getSkinData(), $player->getSkin()->getCapeData(), $player->getSkin()->getGeometryName(), $player->getSkin()->getGeometryData()));
        $human->setNametagVisible(true);
        $human->setNameTagAlwaysVisible(true);
        $human->setImmobile(true);
        $human->spawnToAll();
    }

    /**
     * @param Level $level
     */
    public function purgeEntity(Level $level)
    {
        foreach ($level->getEntities() as $entity) {
            $entity->kill();
        }
    }


    public static function applyNames($server, $tag): void
    {
        $level = GreekNetwork::getInstance()->getServer()->getLevelByName(Settings::$lobby);
        $ip = "play.greekmc.net";
        foreach ($level->getEntities() as $entity) {
            switch ($server) {
                case "hcf":
                    if ($entity instanceof Npc) {
                        if ($entity->getSkin()->getSkinId() == "hcf") {
                            $entity->setNameTag($tag);
                        }
                    }
                    break;
            }
        }

    }
}