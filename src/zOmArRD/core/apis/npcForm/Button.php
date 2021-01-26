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
namespace zOmArRD\core\apis\npcForm;

use Closure;
use JsonSerializable;
use pocketmine\Player;
use pocketmine\utils\Utils;

class Button implements JsonSerializable
{

    /** @var string */
    private $name;

    /** @var string */
    private $text; //???
    /** @var null */
    private $data = null; //???
    /** @var int */
    private $mode = self::MODE_BUTTON; //???
    private const MODE_BUTTON = 0;
    private const MODE_ON_CLOSE = 1;

    /** @var int */
    private $type = self::TYPE_COMMAND; // ????

    private const TYPE_URL = 0; //???
    private const TYPE_COMMAND = 1;
    private const TYPE_INVALID = 2;

    /** @var Closure|null */
    private $submitListener;

    /**
     * Button constructor.
     * @param string $name
     * @param Closure|null $submitListener
     */
    public function __construct(string $name, ?Closure $submitListener = null)
    {
        $this->name = $name;
        $this->setSubmitListener($submitListener);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Closure|null
     */
    public function getSubmitListener(): ?Closure
    {
        return $this->submitListener;
    }

    /**
     * @param Closure|null $submitListener
     */
    public function setSubmitListener(?Closure $submitListener): void
    {
        if ($submitListener !== null) {
            Utils::validateCallableSignature(function (Player $player) {
            }, $submitListener);
        }

        $this->submitListener = $submitListener;
    }

    /**
     * @param Player $player
     */
    public function executeSubmitListener(Player $player): void
    {
        if ($this->submitListener !== null) {
            ($this->submitListener)($player);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "button_name" => $this->name,
            "text" => $this->text ?? "",
            "data" => $this->data,
            "mode" => $this->mode,
            "type" => $this->type
        ];
    }
}