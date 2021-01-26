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
use InvalidArgumentException;
use pocketmine\entity\Entity;
use pocketmine\form\FormValidationException;
use pocketmine\Player;
use pocketmine\utils\Utils;

class DialogForm
{

    /** @var string */
    private $dialogText;

    /** @var Button[] */
    private $buttons = [];

    /** @var Entity|null */
    private $entity = null;

    /** @var Closure|null */
    private $closeListener = null;

    /**
     * DialogForm constructor.
     * @param string $dialogText
     */
    public function __construct(string $dialogText)
    {
        $this->dialogText = $dialogText;
        DialogFormStore::registerForm($this);

        $this->onCreation();
    }

    /**
     * @return string
     */
    public function getDialogText(): string
    {
        return $this->dialogText;
    }

    /**
     * @param string $dialogText
     */
    public function setDialogText(string $dialogText): void
    {
        $this->dialogText = $dialogText;

        if ($this->entity !== null) {
            $this->entity->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, $this->dialogText);
        }
    }

    public function addButton(Button $button): void
    {
        $this->buttons[] = $button;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function getCloseListener(): ?Closure
    {
        return $this->closeListener;
    }

    public function setCloseListener(?Closure $closeListener): void
    {
        if ($closeListener !== null) {
            Utils::validateCallableSignature(function (Player $player) {
            }, $closeListener);
        }
        $this->closeListener = $closeListener;
    }

    public function executeCloseListener(Player $player): void
    {
        if ($this->closeListener !== null) {
            ($this->closeListener)($player);
        }
    }

    public function pairWithEntity(Entity $entity): void
    {
        if ($entity instanceof Player) {
            throw new InvalidArgumentException("NpcForms can't be paired with players.");
        }

        if ($this->entity !== null) {
            $this->entity->getDataPropertyManager()->setByte(Entity::DATA_HAS_NPC_COMPONENT, 0);
        }

        if (($otherForm = DialogFormStore::getFormByEntity($entity)) !== null) {
            DialogFormStore::unregisterForm($otherForm);
        }

        $this->entity = $entity;

        $propertyManager = $entity->getDataPropertyManager();
        $propertyManager->setByte(Entity::DATA_HAS_NPC_COMPONENT, 1);
        $propertyManager->setString(Entity::DATA_INTERACTIVE_TAG, $this->dialogText);
        $propertyManager->setString(Entity::DATA_NPC_ACTIONS, json_encode($this->buttons));
    }

    public function handleResponse(Player $player, $response): void
    {
        if ($response === null) {
            $this->executeCloseListener($player);
        } elseif (is_int($response) and array_key_exists($response, $this->buttons)) {
            $this->buttons[$response]->executeSubmitListener($player);
        } else {
            throw new FormValidationException("Couldn't validate DialogForm with response $response");
        }
    }

    protected function onCreation(): void
    {
    }

}