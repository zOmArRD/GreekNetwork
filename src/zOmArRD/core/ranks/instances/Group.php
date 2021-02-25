<?php
namespace zOmArRD\core\ranks\instances;

use zOmArRD\core\ranks\GreekRanks;

/**
 * Class Group
 * @package zOmArRD\core\ranks\instances
 */
class Group
{
    private $id;

    /**
     * Group constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array|mixed
     */
    public function getInfo()
    {
        $all = GreekRanks::$database->getAll();
        if (isset($all["groups"]["" . $this->id])) {
            return $all["groups"]["" . $this->id];
        } else {
            return ["name" => "", "format" => "", "permissions" => []];
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
     * @param string $permission
     */
    public function addPermission(string $permission)
    {
        $all = GreekRanks::$database->getAll();
        $all["groups"][$this->id . ""]["permissions"][] = $permission;
        GreekRanks::$database->setAll($all);
        GreekRanks::$database->save();
    }

    /**
     * @param string $permission
     */
    public function removePermission(string $permission)
    {
        $all = GreekRanks::$database->getAll();
        if (($key = array_search($permission, $all["groups"][$this->id . ""]["permissions"])) !== false) {
            unset($all["groups"][$this->id . ""]["permissions"][$key]);
        }
        GreekRanks::$database->setAll($all);
        GreekRanks::$database->save();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getInfo()["name"];
    }

    /**
     * @param string $name
     * @return false|Group
     */
    public static function getGroupByName(string $name)
    {
        $all = GreekRanks::$database->getAll();
        $groups = $all["groups"];
        for ($i = 0; $i < count($groups); $i++) {
            if ($groups[$i]["name"] == $name) {
                return new self($i);
            }
        }
        return false;
    }
}