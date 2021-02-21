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
            'software' => ['type' => self::STR, 'required' => true],
            'category' => ['type' => self::STR, 'required' => true],
            'game_type' => ['type' => self::STR, 'required' => true],
            'os' => ['type' => self::STR, 'required' => true],
            'dlc' => ['type' => self::STR, 'required' => true],
            'content_pack' => ['type' => self::STR, 'required' => true],
            'games' => ['type' => self::STR, 'required' => true],
            'price' => ['type' => self::UINT, 'required' => true],
            'link' => ['type' => self::STR, 'required' => true],
            'attachment' => ['type' => self::STR, 'required' => true],
            'user_id' => ['type' => self::UINT, 'required' =>true ],
        ];
        $structure->getters = [];

        return $structure;
    }
}