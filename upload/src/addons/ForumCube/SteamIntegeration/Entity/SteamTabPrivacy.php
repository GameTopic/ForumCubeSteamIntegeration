<?php

namespace ForumCube\SteamIntegeration\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class SteamTabPrivacy extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_fc_steam_tab_privacy';
        $structure->shortName = 'ForumCube\SteamTabPrivacy:TabPrivacy';
        $structure->primaryKey = 'steam_privacy_id';
        $structure->columns = [
            'steam_privacy_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'visitor_username' => ['type' => self::STR, 'required' => true],
        ];
        $structure->getters = [];

        return $structure;
    }
}