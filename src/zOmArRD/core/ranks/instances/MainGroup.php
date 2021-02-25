<?php
namespace zOmArRD\core\ranks\instances;

use zOmArRD\core\ranks\GreekRanks;

/**
 * Class MainGroup
 * @package zOmArRD\core\ranks\instances
 */
class MainGroup
{
    private $id;

    /**
     * MainGroup constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->getInfo()["format"];
    }

    /**
     * @return mixed
     */
    public function getMessageFormat()
    {
        return $this->getInfo()["message-format"];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getInfo()["name"];
    }

    /**
     * @param string $permission
     */
    public function addPermission(string $permission)
    {
        $all = GreekRanks::$database->getAll();
        $all["main-groups"][$this->id . ""]["permissions"][] = $permission;
        GreekRanks::$database->setAll($all);
        GreekRanks::$database->save();
    }

    /**
     * @param string $permission
     */
    public function removePermission(string $permission)
    {
        $all = GreekRanks::$database->getAll();
        if (($key = array_search($permission, $all["main-groups"][$this->id . ""]["permissions"])) !== false) {
            unset($all["main-groups"][$this->id . ""]["permissions"][$key]);
        }
        GreekRanks::$database->setAll($all);
        GreekRanks::$database->save();
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        $all = GreekRanks::$database->getAll();
        if (!isset($all["main-groups"]["" . $this->id])) {
            throw new \Error("The main group for a player was deleted.");
        } else {
            return $all["main-groups"]["" . $this->id];
        }
    }

    /**
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->getInfo()["permissions"];
    }

    /**
     * @param string $name
     * @return false|MainGroup
     */
    public static function getMainGroupByName(string $name)
    {
        $all = GreekRanks::$database->getAll();
        $groups = $all["main-groups"];
        for ($i = 0; $i < count($groups); $i++) {
            if ($groups[$i]["name"] == $name) {
                return new self($i);
            }
        }
        return false;
    }
}