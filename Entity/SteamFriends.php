<?php

namespace ForumCube\SteamIntegeration\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class SteamFriends extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_fc_steam_friends';
        $structure->shortName = 'ForumCube\SteamGame:Friends';
        $structure->primaryKey = 'steam_friends_id';
        $structure->columns = [
            'steam_friends_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'friend' => ['type' => self::STR, 'required' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
        ];
        $structure->getters = [];

        return $structure;
    }
}