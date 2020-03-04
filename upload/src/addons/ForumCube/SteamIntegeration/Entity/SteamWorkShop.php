<?php

namespace ForumCube\SteamIntegeration\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class SteamWorkShop extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_fc_steam_workshop';
        $structure->shortName = 'ForumCube\SteamWorkShop:WorkShop';
        $structure->primaryKey = 'workshop_id';
        $structure->columns = [
            'workshop_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'name' => ['type' => self::STR, 'required' => true],
            'link' => ['type' => self::STR, 'required' => true],
            'attachment' => ['type' => self::STR, 'required' => true],
            'user_id' => ['type' => self::UINT, 'required' =>true ],
        ];
        $structure->getters = [];

        return $structure;
    }
}
