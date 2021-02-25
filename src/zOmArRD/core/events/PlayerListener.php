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
use pocketmine\event\inventory\InventoryTransactionEvent as ITE;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent as PCE;
use pocketmine\event\player\PlayerExhaustEvent as PEE;
use pocketmine\event\player\PlayerJoinEvent as PJE;
use pocketmine\event\player\PlayerQuitEvent as PQE;
use pocketmine\level\Position;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\config\Settings;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\mysql\AsyncQueue;
use zOmArRD\core\mysql\InsertQuery;
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
        $server = Settings::$server;

        /** Necessary when player join */
        PlayerUtils::onPJE($player);
        PlayerUtils::sendSC($player);
        PlayerUtils::giveItems($player);

        PlayerUtils::sendFloatingText02($player);
        PlayerUtils::sendFloatingText03($player);

        AsyncQueue::submitQuery(new InsertQuery("UPDATE servers SET players = players+1 WHERE server='{$server}'"));

        $player->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, GreekNetwork::getInstance()->getServer()->getLevelByName(Settings::$lobby)));
        $player->setAllowFlight(true);

        $e->setJoinMessage(null);
        $player->sendMessage(Settings::$joinMessage);
    }

    /**
     * @param PQE $e
     */
    public function onPLE(PQE $e): void
    {
        $player = $e->getPlayer();
        $server = Settings::$server;

        AsyncQueue::submitQuery(new InsertQuery("UPDATE servers SET players = players-1 WHERE server='{$server}'"));

        $e->setQuitMessage(null);
        $player->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, GreekNetwork::getInstance()->getServer()->getLevelByName(Settings::$lobby)));
    }

    /**
     * @param EDE $e
     */
    public function onEDE(EDE $e): void
    {
        $e->setCancelled(true);
    }

    public function onPEE(PEE $e): void
    {
        $e->setCancelled(true);
    }

    /**
     * @param PCE $e
     */
    public function onPCE(PCE $e)
    {
        $pl_name = $e->getPlayer()->getName();
        $message = $e->getMessage();
        $e->setFormat("§6" . $pl_name . "§7: " . $message);
    }

    /**
     * @param ITE $e
     */
    public function onITE(ITE $e): void
    {
        $entity = $e->getTransaction()->getSource();

        if ($entity->getLevel()->getName() === Settings::$lobby) {
            $e->setCancelled(true);
            if ($entity->isOp()) {
                $e->setCancelled(false);
            }
        }
    }
}