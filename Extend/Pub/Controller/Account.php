<?php

namespace ForumCube\SteamIntegeration\Extend\Pub\Controller;

use XF\Mvc\ParameterBag;

class Account extends XFCP_Account
{
    /**
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\PrintableException
     */
    public function actionSteamPrivacy( )
    {
        $allUsers = $this->getSteamTabPrivacyRepo()
                          ->getAllSteamTabUsers();

        $users = $this->getUserRepo()->getUsersByNames( $allUsers->toArray() );

        $viewParams = [
            'users' => $users
        ];

        if( $this->isPost() )
        {
            $input = $this->filter(['visitor_username' => 'str']);
            $input['user_id'] = \XF::visitor()->user_id;

            if( $input['visitor_username'] == \XF::visitor()->username )
            {
                return $this->error( \XF::Phrase('fc_friend_is_visitor') );
            }

	    $user = $this->getSteamTabPrivacyRepo()
                           ->checkUserExist( $input['visitor_username'] );

            if( $user )
            {
                return $this->error( \XF::Phrase('fc_already_exist') );
            }	 

            $steamTab = $this->em()->create('ForumCube\SteamIntegeration:SteamTabPrivacy');
            $steamTab->bulkSet( $input );
            $steamTab->save();

            return $this->redirect($this->getDynamicRedirect(), 'save');
        }

        $view = $this->view('', 'fc_steam_data_privacy', $viewParams);
        return $this->addAccountWrapperParams($view, 'steam-privacy');
    }

    public function actionRemoveUserAccess()
    {
        $username = $this->filter('user','str');

        if ( $this->isPost() ) {

            $username = $this->filter('username','str');

            $this->getSteamTabPrivacyRepo()->removeUserAccess( $username );

            return $this->redirect( $this->buildLink('account/steam-privacy') );
        }


        $viewParams = [
            'username' => $username,
            'route' => 'account/remove-user-access'
        ];

        return $this->view('', 'fc_confirm_remove', $viewParams);
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
    private function getSteamTabPrivacyRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamTabPrivacy');
    }
}
