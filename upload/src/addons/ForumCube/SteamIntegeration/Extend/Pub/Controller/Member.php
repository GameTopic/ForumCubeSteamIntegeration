<?php

namespace ForumCube\SteamIntegeration\Extend\Pub\Controller;

use XF\Mvc\ParameterBag;
use ForumCube\SteamIntegeration\SteamApi\Steam;
use XF\Util\File;

class Member extends XFCP_Member
{
    /**
     * @return \XF\Mvc\Reply\View
     * added new tab to render data for steam game this will check
     * the user preference steam data display check visitor permissions
     * to view the user steam profile data
     */
    public function actionSteamGame( ParameterBag $params )
    {
        $permission = $this->getVisitorPermission();

        $userAccess = true;

        if( \XF::visitor()->user_id != $params->user_id )
        {
            $userAccess = $this->checkUserTabAccess($params->user_id);
        }

        if( !$permission || !$userAccess )
        {
            return $this->noPermission();
        }

        $allGames = $this->getSteamGameRepo()
                         ->getAllGames( $params->user_id );

        $user = $this->assertViewableUser( $params->user_id );

        $viewParams = [
            'allGames' => $allGames,
            'user' => $user
        ];

        if ( isset( $user->Profile->connected_accounts['steam'] ) &&
             $user->Profile->connected_accounts['steam'] /*&& $user->Option->fc_steam_data*/ )
        {
            $steamApi = new Steam( $user );
            $steamGames = $steamApi->getGames();

            $viewParams[
            'steamGames'] = $steamGames;
        }


        return $this->view('','fc_steam_game',$viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\View
     * adding new steam game in xf side
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionAddSteamGame()
    {
        if( $this->isPost() )
        {
            $attachment = $this->request->getFile('attachment', false, false);

            if( $attachment )
            {
                $this->validationCheck( $attachment );

                 $attachment = $this->uploadAttachment( $attachment );

                 $input = $this->filter(['name' => 'str',
                                        'link' => 'str']);
                 $input['user_id'] = \XF::visitor()->user_id;
                 $input['attachment'] = $attachment;

                 $game = $this->em()->create('ForumCube\SteamIntegeration:SteamGame');
                 $game->bulkSet( $input );
                 $game->save();

                 return $this->redirect( $this->buildLink('members/'.$input['user_id'].'/#steam-game') );
            }

        }

        return $this->view('','fc_add_steam_game');
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     */
    public function actionRemove( ParameterBag $params )
    {
        $gameId = $this->filter('game','uint');

        $workShopId = $this->filter('workshop','uint');

        if ( $this->isPost() )
        {
            $gameId = $this->filter('game','uint');
            $userId = $this->filter('user_id','uint');
            $workShopId = $this->filter('workShop','uint');

            if( $gameId ) {
                $this->getSteamGameRepo()
                    ->getUserGameById($userId, $gameId)
                    ->delete();
            
            return $this->redirect( $this->buildLink('members/'.$userId.'/#steam-game') );	

	    }
            elseif( $workShopId ){

                $this->getSteamWorkShopRepo()
                     ->getUserWorkshopByIds($userId,$workShopId)
                     ->delete();
       
	    return $this->redirect( $this->buildLink('members/'.$userId.'/#steam-workshop') );
            }

            return $this->redirect( $this->getDynamicRedirect() );
        }

        $viewParams = [
            'game' => $gameId,
            'workShop' => $workShopId,
            'user_id' => $params->user_id,
            'route' => 'members/remove'
        ];

        return $this->view('', 'fc_confirm_remove', $viewParams);

    }

    /**
     * @return \XF\Mvc\Reply\View
     * added new tab to render data for
     * steam workshop
     */
    public function actionSteamWorkShop( ParameterBag $params )
    {
        $permission = $this->getVisitorPermission();

        $userAccess = true;

        if( \XF::visitor()->user_id != $params->user_id )
        {
            $userAccess = $this->checkUserTabAccess($params->user_id);
        }

       if( !$permission || !$userAccess )
        {
            return $this->noPermission();
        }

        $allWorkShops = $this->getSteamWorkShopRepo()
                             ->getAllWorkShops( $params->user_id );

        $user = $this->assertViewableUser( $params->user_id );

        $viewParams = [
            'allWorkShops' => $allWorkShops,
            'user'  => $user
        ];

        return $this->view('','fc_steam_workshop',$viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\View
     * adding new steam game in xf side
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionAddSteamWorkShop()
    {
        if( $this->isPost() )
        {
            $attachment = $this->request->getFile('attachment', false, false);

            if( $attachment )
            {
                $this->validationCheck( $attachment );

                $attachment = $this->uploadAttachment( $attachment );

                $input = $this->filter(['name' => 'str',
                                        'link' => 'str']);
                $input['user_id'] = \XF::visitor()->user_id;
                $input['attachment'] = $attachment;

                $game = $this->em()->create('ForumCube\SteamIntegeration:SteamWorkShop');
                $game->bulkSet( $input );
                $game->save();

                return $this->redirect( $this->buildLink('members/'.$input['user_id'].'/#steam-workshop') );
            }

        }

        return $this->view('','fc_add_steam_workshop');
    }

    /**
     * @return \XF\Mvc\Reply\View
     * added new tab to render data for
     * steam friends
     */
    public function actionSteamFriends( ParameterBag $params )
    {
        $permission = $this->getVisitorPermission();
        $userAccess = true;

        if( \XF::visitor()->user_id != $params->user_id )
        {
            $userAccess = $this->checkUserTabAccess($params->user_id);
        }

        if( !$permission || !$userAccess )
        {
            return $this->noPermission();
        }

        $allFriends = $this->getSteamFriendsRepo()
            ->getAllSteamFriends($params->user_id);

        $users = $this->getUserRepo()->getUsersByNames($allFriends->toArray());

        $user = $this->assertViewableUser( $params->user_id );

        $viewParams = [
            'users' => $users,
            'currentUser' => $user
        ];

        $user = $this->assertViewableUser( $params->user_id );

        if ( isset( $user->Profile->connected_accounts['steam'] ) &&
            $user->Profile->connected_accounts['steam'] && $user->Option->fc_steam_data )
        {
            $steamApi = new Steam( $user );
            $steamFriends = $steamApi->getFriends();
            $viewParams[
                'steamFriends'] = $steamFriends['response']['players'];
        }

        return $this->view('','fc_steam_friends',$viewParams);
    }

    public function actionAddSteamFriends()
    {
        if ( $this->isPost() )
        {
            $input = $this->filter(['friend' => 'str']);
            $input['user_id'] = \XF::visitor()->user_id;
	
	    if( $input['friend'] == \XF::visitor()->username )
        	{
		  return $this->error( \XF::Phrase('fc_friend_is_visitor') );
		}
	

            $friend = $this->getSteamFriendsRepo()
                           ->checkFriendExist( $input['friend'] );

            if( $friend )
            {
                return $this->error( \XF::Phrase('fc_already_exist') );
            }

            $friend = $this->em()->create('ForumCube\SteamIntegeration:SteamFriends');
            $friend->bulkSet( $input );
            $friend->save();

            return $this->redirect( $this->buildLink('members/'.$input['user_id'].'/#steam-friends') );

        }

        return $this->view('','fc_add_steam_friend');
    }

    public function actionRemoveFriend( ParameterBag $params )
    {
        $username = $this->filter('user','str');

        if ( $this->isPost() ) {

            $username = $this->filter('username','str');
            $user = $this->filter('user_id','uint');

            $this->getSteamFriendsRepo()
                 ->getUserFriendByIds( $username, $user )
                 ->delete();

           return $this->redirect( $this->buildLink('members/'.$user.'/#steam-friends') );
        }


        $viewParams = [
            'username' => $username,
            'user_id' => $params->user_id,
            'route' => 'members/remove-friend'
        ];

        return $this->view('', 'fc_confirm_remove', $viewParams);
    }

    /**
     * @param $attachment
     * @return string|\XF\Mvc\Reply\Error
     */
    public function uploadAttachment( $attachment )
    {
        $fileWithExtension = \XF::$time .'.' .$attachment->getExtension();

        $path = 'data://ForumCube/images/'.$fileWithExtension;

        File::copyFileToAbstractedPath( $attachment->getTempFile(), $path );

        return  $fileWithExtension;
    }

    /**
     * @param $attachment
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function validationCheck( $attachment )
    {
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'];

        if ( !$attachment )
        {
            throw $this->exception($this->notFound( \XF::phrase('fc_required_images') ));
        }
        if ( !in_array( $attachment->getExtension(), $allowedExtensions) )
        {
            throw $this->exception($this->error(\XF::phrase('fc_required_extentions')));
        }

        return;
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getUserRepo()
    {
        return $this->repository('XF:User');
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getSteamGameRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamGames');
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getSteamWorkShopRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamWorkShop');
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getSteamFriendsRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamFriends');
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getSteamTabPrivacyRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamTabPrivacy');
    }

    private function getVisitorPermission()
    {
        return \XF::visitor()->hasPermission( "fcSteam",
                                              "fcSteamContent"
                                            );
    }

    private function checkUserTabAccess( $userId )
    {
       $data =  $this->getSteamTabPrivacyRepo()
                     ->checkUserAccess( $userId );

       if( $data )
       {
           return true;
       }

       return false;
    }
}
