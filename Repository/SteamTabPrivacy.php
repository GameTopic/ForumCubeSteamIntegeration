<?php

namespace ForumCube\SteamIntegeration\Repository;

use XF\Mvc\Entity\Repository;

class SteamTabPrivacy extends Repository
{
    public function getAllSteamTabUsers()
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamTabPrivacy')
                    ->pluckFrom('visitor_username')
                    ->where( 'user_id', \XF::visitor()->user_id )
                    ->fetch();
    }

    public function checkUserAccess( $userId )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamTabPrivacy')
                    ->where( 'visitor_username', \XF::visitor()->username )
                    ->where( 'user_id' , $userId )
                    ->fetchOne();
    }

    public function removeUserAccess( $username )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamTabPrivacy')
                    ->where( 'visitor_username', $username )
                    ->where( 'user_id' , \XF::visitor()->user_id )
                    ->fetchOne()
                    ->delete();
    }

   public function checkUserExist( $user )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamTabPrivacy')
                    ->where( 'visitor_username', $user )
		->where('user_id', \XF::visitor()->user_id)     
               ->fetchOne();
    }

}
