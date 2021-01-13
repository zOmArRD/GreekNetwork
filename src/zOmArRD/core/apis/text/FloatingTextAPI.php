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
namespace zOmArRD\core\apis\text;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

/**
 * Class FloatingTextApi
 * @package zOmArRD\core\apis\text
 */
class FloatingTextApi {

    /** @var array $texts */
    private static $texts = [];

    /**
     * @param Vector3 $pos
     * @return int
     */
    public static function createText(Vector3 $pos): int {
        $eid = Entity::$entityCount++;

        $pk = new AddPlayerPacket();
        $pk->username = "Text";
        $pk->uuid = UUID::fromRandom();
        $pk->entityRuntimeId = $eid;
        $pk->entityUniqueId = $eid;
        $pk->position = $pos;
        $pk->item = Item::get(0);
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_IMMOBILE],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]
        ];

        self::$texts[$eid] = $pk;

        return $eid;
    }

    /**
     * @param int $eid
     * @param Player $player
     * @param string $text
     */
    public static function sendText(int $eid, Player $player, string $text = "Text") {
        /** @var AddPlayerPacket $pk */
        $pk = clone self::$texts[$eid];
        $pk->username = $text;

        $player->dataPacket($pk);
    }

    /**
     * @param int $eid
     * @param Player $player
     */
    public static function removeText(int $eid, Player $player) {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $eid;
        $player->dataPacket($pk);
    }
}