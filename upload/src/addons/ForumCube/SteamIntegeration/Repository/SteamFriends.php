<?php

namespace ForumCube\SteamIntegeration\Repository;
use XF\Mvc\Entity\Repository;

class SteamFriends extends Repository
{
    /**
     * @param $userId
     * @return \XF\Mvc\Entity\ArrayCollection
     * return all steam friends on xf side
     * of the current user
     */
    public function getAllSteamFriends( $userId )
    {
       return $this->finder('ForumCube\SteamIntegeration:SteamFriends')
                   ->pluckFrom('friend')
                   ->where( 'user_id', $userId )
                   ->fetch();
    }

    /**
     * @param $friend
     * @param $userId
     * @return \XF\Mvc\Entity\Entity|null
     */
    public function getUserFriendByIds( $friend,$userId )
    {

        return $this->finder('ForumCube\SteamIntegeration:SteamFriends')
                    ->where( 'friend',$friend )
                    ->where( 'user_id', $userId )
                    ->fetchOne();
    }

    /**
     * @param $friend
     * @return \XF\Mvc\Entity\Entity|null
     * check if friend already exist
     */
    public function checkFriendExist( $friend )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamFriends')
                    ->where( 'friend',$friend )
                    ->fetchOne();
    }

}
