<?php

namespace ForumCube\SteamIntegeration\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class SteamGame extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_fc_steam_game';
        $structure->shortName = 'ForumCube\SteamGame:Game';
        $structure->primaryKey = 'game_id';
        $structure->columns = [
            'game_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'name' => ['type' => self::STR, 'required' => true],
            'link' => ['type' => self::STR, 'required' => true],
            'attachment' => ['type' => self::STR, 'required' => true],
            'user_id' => ['type' => self::UINT, 'required' =>true ],
        ];
        $structure->getters = [];

        return $structure;
    }
}