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

use InvalidArgumentException;
use pocketmine\entity\Entity;

class DialogFormStore
{

    /** @var DialogForm[] */
    static private $forms = [];

    /**
     * @param Entity $entity
     * @return DialogForm|null
     */
    static public function getFormByEntity(Entity $entity): ?DialogForm
    {
        foreach (self::$forms as $form) {
            if ($form->getEntity() === $entity) {
                return $form;
            }
        }
        return null;
    }

    /**
     * @param DialogForm $form
     */
    static public function registerForm(DialogForm $form): void
    {
        if (in_array($form, self::$forms)) {
            throw new InvalidArgumentException("Trying to overwrite an already registered npc form");
        }
        self::$forms[] = $form;
    }

    /**
     * @param DialogForm $form
     */
    static public function unregisterForm(DialogForm $form): void
    {
        if (($key = array_search($form, self::$forms)) !== false) {
            unset(self::$forms[$key]);
        } else {
            throw new InvalidArgumentException("Tried to unregister a dialog form that wasn't registered");
        }
    }
}