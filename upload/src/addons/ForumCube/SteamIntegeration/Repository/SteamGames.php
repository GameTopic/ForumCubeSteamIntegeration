<?php

namespace ForumCube\SteamIntegeration\Repository;
use XF\Mvc\Entity\Repository;

class SteamGames extends Repository
{
    /**
     * @param $userId
     * @return \XF\Mvc\Entity\ArrayCollection
     * return all steam games on xf side
     * of the current user
     */
    public function getAllGames( $userId )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamGame')
                    ->where( 'user_id' , $userId )
                    ->fetch();
    }

    public function getUserGameById( $userId , $gameId )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamGame')
                    ->where( 'user_id' , $userId )
                    ->where( 'game_id' , $gameId )
                    ->fetchOne();
    }
}