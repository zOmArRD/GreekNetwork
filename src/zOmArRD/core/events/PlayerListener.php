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
namespace zOmArRD\core\events;

use pocketmine\event\entity\EntityDamageEvent as EDE;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerExhaustEvent as PEE;
use pocketmine\event\player\PlayerJoinEvent as PJE;
use pocketmine\event\player\PlayerQuitEvent as PQE;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\utils\Binary;
use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\utils\PlayerUtils;

class PlayerListener implements Listener
{
    /**
     * Function when Player join to the Server
     * @param PJE $e
     */
    public function onPJE(PJE $e): void
    {
        $player = $e->getPlayer();
        $name = $player->getName();

        /** Necessary when player join */
        PlayerUtils::onPJE($player);
        $player->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, GreekNetwork::getInstance()->getServer()->getLevelByName(Settings::$lobby)));

        $e->setJoinMessage(null);
        $player->sendMessage(Settings::$joinMessage);
    }

    /**
     * @param PQE $e
     */
    public function onPLE(PQE $e): void
    {
        $player = $e->getPlayer();
        $e->setQuitMessage(null);
        //PlayerUtils::getSafeSpawn($player);
    }

    /**
     * @param EDE $e
     */
    public function on(EDE $e): void
    {
        $e->setCancelled(true);
    }

    public function onPEE(PEE $e): void
    {
        $e->setCancelled(true);
    }

    public function player(PlayerChatEvent $event){
        if ($event->getMessage() === "hcf"){
            $event->getPlayer();
            self::transferPlayer($event->getPlayer(), "hcf");
        }
    }

    public static function transferPlayer(Player $player, String $server): bool
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect")) . "Connect" . Binary::writeShort(strlen($server)) . $server;
        $player->sendDataPacket($pk);
        return true;
    }
}