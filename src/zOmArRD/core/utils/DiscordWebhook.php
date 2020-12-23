<?php
declare(strict_types=1);

namespace zOmArRD\core\utils;

use zOmArRD\core\GreekNetwork;
use zOmArRD\core\web\discord\Embed;
use zOmArRD\core\web\discord\Message;
use zOmArRD\core\web\discord\Webhook;

class DiscordWebhook
{
    public static function onEnable(): void
    {
        /** @var  $webHook */
        $webHook = new Webhook("https://discord.com/api/webhooks/787154034346819615/SwY-fREJ9hacLkoatmeQQC94SqtjylR5vt--Is7-aMx9-sOVLBkvtfHjFARTKZGklhU3");

        /** @var  $msg */
        $msg = new Message();

        /** @var  $embed */
        $embed = new Embed();

        /** @var  $br */
        $br = "\n";

        /** @var  $br2 */
        $br2 = "\n" . "\n";

        $players = count(GreekNetwork::getInstance()->getServer()->getOnlinePlayers());

        $embed->setTitle("Greek Network");
        $embed->setColor(0x00FF00);
        $embed->setDescription("Status: " . "**Online**" . $br2 . "Players: " . "**$players**" . $br2 . "greekhcf.ddns.net join now!");
        $msg->addEmbed($embed);
        $webHook->send($msg);
    }
}