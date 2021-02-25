<?php
namespace zOmArRD\core\ranks\instances;

use zOmArRD\core\ranks\GreekRanks;

class Tag
{
    private $id;

    /**
     * Tag constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed|string
     */
    public function getFormat()
    {
        return $this->getInfo()["format"];
    }

    /**
     * @return mixed|string[]
     */
    public function getInfo()
    {
        $all = GreekRanks::$database->getAll();
        if (isset($all["tags"]["" . $this->id])) {
            if (!isset($all["tags"]["" . $this->id]["permission"])) {
                $all["tags"]["" . $this->id]["permission"] = "";
            }
            return $all["tags"]["" . $this->id];

        } else {
            return ["name" => "", "format" => "", "permission" => ""];
        }
    }

    /**
     * @return mixed|string
     */
    public function getPermission()
    {
        return $this->getInfo()["permission"];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
