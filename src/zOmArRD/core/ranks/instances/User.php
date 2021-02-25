<?php
declare(strict_types=1);

namespace zOmArRD\core\ranks\instances;

use pocketmine\Player;
use zOmArRD\core\config\Settings;
use zOmArRD\core\ranks\GreekRanks;

class User
{
    private $player;
    private $online;

    public function __construct($player)
    {
        if ($player instanceof Player) {
            $this->player = $player;
            $this->online = true;
        } else {
            $this->player = $player;
            $this->online = false;
        }
    }

    public function getUserMainGroup()
    {
        $info = $this->getPlayerInfo();
        return new MainGroup($info["maingroup"]);
    }

    public function getPlayerFormat()
    {
        $maingroup = $this->getUserMainGroup();
        return $maingroup->getFormat();
    }

    public function getNick(): string
    {
        $userinfo = $this->getPlayerInfo();
        if (isset($userinfo["nick"])) return $userinfo["nick"];
        return $this->getName();
    }

    public function resetNick(): void
    {
        $name = $this->getName();
        Settings::$con->query("UPDATE ranks SET nick='$name' WHERE ign='$name'");
        $this->startPlayer();
    }

    public function setNick(string $nick): void
    {
        $name = $this->getName();
        $smtp = Settings::$con->prepare("UPDATE ranks SET nick=? WHERE ign='$name'");
        $smtp->bind_param("s", $nick);
        $smtp->execute();
        $this->startPlayer();
    }

    public function getPlayerGroups(): array
    {
        return $this->getPlayerInfo()["groups"];
    }

    public function getPlayerTags(): array
    {
        return $this->getPlayerInfo()["tags"];
    }


    public function getTag(int $num): string
    {
        $rowa = "tag" . $num;
        $name = $this->getName();
        $res = Settings::$con->query("SELECT * FROM ranks WHERE ign='$name'");
        $row = $res->fetch_assoc();
        $id = $row[$rowa];
        if (is_numeric($id)) {
            $tag = new Tag($id);
        } else {
            $tag = $id == null ? "" : $id;
        }
        return $tag instanceof Tag ? $tag->getFormat() : $tag;
    }

    public function getTagInstance(int $num): string
    {
        $rowa = "tag" . $num;
        $name = $this->getName();
        $res = Settings::$con->query("SELECT * FROM ranks WHERE ign='$name'");
        $row = $res->fetch_assoc();
        $id = $row[$rowa];
        if (is_numeric($id)) {
            $tag = new Tag($id);
        } else {
            $tag = $id == null ? "" : $id;
        }
        return $tag;
    }

    public function setTag($num, $tag): void
    {
        if ($num == "1" || "2" || "3") {
            $row = "tag" . $num;
            if ($tag instanceof Tag) {
                $tagg = $tag->getId();
            } else {
                $tagg = $tag;
            }
            $name = $this->getName();
            Settings::$con->query("UPDATE ranks SET $row='$tagg' WHERE ign='$name'");
            $this->startPlayer();
        }
    }

    public function removeTag($num): void
    {
        if ($num == "1" || "2" || "3") {
            $row = "tag" . $num;
            $name = $this->getName();
            Settings::$con->query("UPDATE ranks SET $row=null WHERE ign='$name'");
            $this->startPlayer();
        }
    }

    public function removeGroup($num): void
    {
        if ($num == 2 || 1) {
            $row = "group" . $num;
            $name = $this->getName();
            Settings::$con->query("UPDATE ranks SET $row=null WHERE ign='$name'");
            $this->startPlayer();
        }
    }

    public function getMessageFormat(): string
    {
        $format = $this->getUserMainGroup()->getMessageFormat();
        return self::replaceVars($format, ["{{name}}" => $this->getNick(), "{{group1}}" => $this->getGroup(1), "{{group2}}" => $this->getGroup(2), "{{tag1}}" => $this->getTag(1), "{{tag2}}" => $this->getTag(2), "{{tag3}}" => $this->getTag(3)]);
    }

    public function startPlayer(): void
    {
        $pl = $this->player;
        if ($this->online) {
            GreekRanks::$chat[$pl->getName()] = $this->getMessageFormat();
            $pl->setNameTag(self::replaceVars($this->getPlayerFormat(), ["{{name}}" => $this->getNick(), "{{group1}}" => $this->getGroup(1), "{{group2}}" => $this->getGroup(2), "{{tag1}}" => $this->getTag(1), "{{tag2}}" => $this->getTag(2), "{{tag3}}" => $this->getTag(3)]));
            $this->reloadPermissions();
        }
    }

    public function reloadPermissions(): void
    {
        $this->clearPermissions();
        foreach ($this->getPlayerPermissions() as $perm) {
            $this->applyPermission($perm);
        }
    }

    public function getPlayerPermissions(): array
    {
        return array_merge($this->getPermissions(), $this->getUserMainGroup()->getPermissions(), $this->getGroupPermissions(1), $this->getGroupPermissions(2));
    }

    public function hasGroup(Group $group): bool
    {
        $groups = $this->getPlayerGroups();
        return isset($groups[$group->getId()]);
    }

    public function getGroup(int $num): string
    {
        $rowa = "group" . $num;
        $name = $this->getName();
        $res = Settings::$con->query("SELECT * FROM ranks WHERE ign='$name'");
        $row = $res->fetch_assoc();
        $group = new Group($row[$rowa]);
        return $group->getFormat();
    }

    public function getGroupInstance(int $num)
    {
        $rowa = "group" . $num;
        $name = $this->getName();
        $res = Settings::$con->query("SELECT * FROM ranks WHERE ign='$name'");
        $row = $res->fetch_assoc();
        return new Group($row[$rowa]);
    }

    public function getGroupPermissions(int $num): array
    {
        $rowa = "group" . $num;
        $name = $this->getName();
        $res = Settings::$con->query("SELECT * FROM ranks WHERE ign='$name'");
        $row = $res->fetch_assoc();
        $group = new Group($row[$rowa]);
        return $group->getPermissions();
    }

    public function setGroup($num, Group $group): void
    {
        if ($num == 2 || 1) {
            $row = "group" . $num;
            $id = $group->getId();
            $name = $this->getName();
            Settings::$con->query("UPDATE ranks SET $row=$id WHERE ign='$name'");
            $this->startPlayer();
        }
    }

    public function setMainGroup(MainGroup $group): void
    {
        $id = $group->getId();
        $name = $this->getName();
        Settings::$con->query("UPDATE ranks SET maingroup=$id WHERE ign='$name'");
        $this->startPlayer();
    }

    public static function replaceVars(string $string, array $replacement): string
    {
        $values = array_values($replacement);
        $keys = array_keys($replacement);
        $s = $string;
        for ($i = 0; $i < count($values); $i++) {
            $s = str_replace($keys[$i], $values[$i], $s);
        }
        return $s;
    }

    public function getPlayerInfo()
    {
        $name = $this->getName();
        $res = Settings::$con->query("SELECT * FROM ranks WHERE ign='$name'");
        if ($res->num_rows === 0) {
            $this->init();
        } else {
            return $res->fetch_assoc();
        }
    }

    public function getName()
    {
        return $this->online == true ? $this->player->getName() : $this->player;
    }

    public function addPermission(string $permission): void
    {
        $name = $this->getName();
        $permissions = $this->getPermissions();
        $permissions[] = $permission;
        $string = implode(",", $permissions);
        Settings::$con->query("UPDATE ranks SET permissions='$string' WHERE ign='$name'");
        $this->reloadPermissions();
    }

    public function applyPermission($permission): void
    {
        if ($this->player instanceof Player) {
            GreekRanks::$PermissionsManager->setPerm($this->player)->setPermission($permission, true);
        }
    }

    public function clearPermissions(): void
    {
        if ($this->player instanceof Player) {
            GreekRanks::$PermissionsManager->setPerm($this->player)->clearPermissions();
        }
    }

    public function getPermissions(): array
    {
        $name = $this->getName();
        $res = Settings::$con->query("SELECT permissions FROM ranks WHERE ign='$name'");
        $row = $res->fetch_assoc();
        return explode(",", $row["permissions"]);
    }

    public function removePermission($permission): void
    {
        $name = $this->getName();
        $permissions = $this->getPermissions();
        if (($key = array_search($permission, $permissions)) !== false) {
            unset($permissions[$key]);
        }
        $string = implode(",", $permissions);
        Settings::$con->query("UPDATE ranks SET permissions='$string' WHERE ign='$name'");
        $this->reloadPermissions();
    }

    public function unapplyPermission($permission): void
    {
        GreekRanks::$PermissionsManager->setPerm($this->player)->unsetPermission($permission);
    }

    public function init(): void
    {
        $name = $this->getName();
        $defaultGroup = GreekRanks::$database->get("default");
        Settings::$con->query("INSERT INTO ranks(ign, maingroup) VALUES ('$name', $defaultGroup)");
    }
}