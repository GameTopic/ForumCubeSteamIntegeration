<?php

namespace ForumCube\SteamIntegeration\Repository;
use XF\Mvc\Entity\Repository;

class SteamWorkShop extends Repository
{
    /**
     * @param $userId
     * @return \XF\Mvc\Entity\ArrayCollection
     * return all steam workshops on xf side
     * of the current user
     */
    public function getAllWorkShops( $userId )
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamWorkShop')
                    ->where( 'user_id',$userId )
                    ->fetch();
    }

    public function getUserWorkshopByIds( $userId , $workshopId)
    {
        return $this->finder('ForumCube\SteamIntegeration:SteamWorkShop')
                    ->where( 'user_id',$userId )
                    ->where( 'workshop_id',$workshopId )
                    ->fetchOne();
    }
}