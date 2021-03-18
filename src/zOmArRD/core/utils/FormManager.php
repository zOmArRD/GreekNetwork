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
namespace zOmArRD\core\utils;

use pocketmine\Player;
use zOmArRD\core\addons\Extensions;
use zOmArRD\core\apis\form\SimpleForm;
use zOmArRD\core\GreekNetwork;
use zOmArRD\core\server\Proxy;

class FormManager
{
    public static function serverSelector(Player $player)
    {

        $form = new SimpleForm(function (Player $player, ?int $data) {
            if (!is_null($data)) {
                $error = "§cWe have not connected to the server, please try later";
                switch ($data) {
                    case 0:
                        $player->sendMessage($error);
                        break;
                    case 1:
                        Extensions::getProxy()->transferPlayer($player, "hcf");
                        break;
                    case 2:
                        break;
                }
            }
        });

        $images = [
            "strength" => "textures/gui/newgui/mob_effects/strength_effect",
            "bow" => "textures/items/bow_pulling_1",
            "close" => "textures/gui/newgui/anvil-crossout",
        ];

        $cl = "§a";
        $HCFPlayers = (new FormManager)->getProxy()->getServerPlayers("45.134.8.141", 19132);
        $HCFMaxPlayer = (new FormManager)->getProxy()->getServerMaxPlayers("45.134.8.141", 19132);
        if ($HCFPlayers !== null) {
            $tagHCF = $cl . $HCFPlayers . "/" . $HCFMaxPlayer . " PLAYING";
        } else {
            $tagHCF = "§cOFFLINE";
        }

        $br = "\n";
        $form->setTitle("§l§7» §6Greek §8Network §7«");
        $form->setContent("§eSelect which server you want to transfer to");

        $form->addButton("§l§6SkyWars §r§7(NA)" . $br . "§cOFFLINE", 0, $images['bow']);
        $form->addButton("§l§dHCF §r§7(NA)" . $br . $tagHCF, 0, $images['strength']);
        $form->addButton("§cClose", 0, $images["close"]);
        $player->sendForm($form);
    }


    /**
     * @return Proxy
     */
    public function getProxy(): Proxy
    {
        return Extensions::getProxy();
    }
}