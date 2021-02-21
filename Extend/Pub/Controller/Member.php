<?php

namespace ForumCube\SteamIntegeration\Extend\Pub\Controller;

use XF\Mvc\ParameterBag;
use ForumCube\SteamIntegeration\SteamApi\Steam;
use XF\Util\File;

class Member extends XFCP_Member
{
    /**
     * added new tab to render data for steam game this will check
     * the user preference steam data display check visitor permissions
     * to view the user steam profi/  /datale data
     * @return \XF\Mvc\Reply\View
     */
    public function actionSteamGame(ParameterBag $params)
    {
        $permission = $this->getVisitorPermission();

        $userAccess = true;

        $userId = ($params->user_id) ? $params->user_id : $this->filter('user_id', 'uint');

        if (\XF::visitor()->user_id != $userId) {
            $userAccess = $this->checkUserTabAccess($userId);
        }

        if( !$permission || !$userAccess ){
            return $this->noPermission();
        }

        /** @var \ForumCube\SteamIntegeration\ControllerPlugin\GameOverview $overviewPlugin */
        $overviewPlugin = $this->plugin('ForumCube\SteamIntegeration:GameOverview');
        $data = $overviewPlugin->getCoreListData($userId);

        $user = $this->assertViewableUser($userId);

        $allGames = $data['steam_games']->fetch();

        $viewParams = [
            'allGames' => $allGames,
            'filters' => $data['filters'],
            'user' => $user
        ];

        if (isset($user->Profile->connected_accounts['steam']) &&
            $user->Profile->connected_accounts['steam'] /*&& $user->Option->fc_steam_data*/) {
            $steamApi = new Steam($user);
            $steamGames = $steamApi->getGames();
            $viewParams['steamGames'] = $steamGames;
        }

        return $this->view('', 'fc_steam_game', $viewParams);
    }

    public function actionFilters()
    {
        $userId = $this->filter('user_id', 'uint');

        /** @var \ForumCube\SteamIntegeration\ControllerPlugin\GameOverview $overviewPlugin */
        $overviewPlugin = $this->plugin('ForumCube\SteamIntegeration:GameOverview');

        return $overviewPlugin->actionFilters(null, $userId);

    }

    public function actionWorkshopFilters()
    {
        $userId = $this->filter('user_id', 'uint');

        /** @var \ForumCube\SteamIntegeration\ControllerPlugin\GameOverview $overviewPlugin */
        $overviewPlugin = $this->plugin('ForumCube\SteamIntegeration:WorkShopOverview');

        return $overviewPlugin->actionFilters(null, $userId);

    }

    /**
     * adding new steam game in xf side
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionAddSteamGame()
    {
        $gameId = $this->filter('game', 'uint');
        if ($this->isPost() && !$gameId) {
            $game = $this->em()->create('ForumCube\SteamIntegeration:SteamGame');
            $this->saveSteamGame($game);
            return $this->redirect($this->buildLink('members/' . \XF::visitor()->user_id . '/#steam-game'));

        } elseif ($this->isPost() && $gameId) {
            $game = $this->getSteamGameRepo()
                ->getUserGameById(\XF::visitor()->user_id, $gameId);

            $this->saveSteamGame($game);
            return $this->redirect($this->buildLink('members/' . \XF::visitor()->user_id . '/#steam-game'));
        }

        $viewParams = ['field_names' => array_unique(
            array_filter(
                \XF::options()->fc_steam_game_dropdown['field_name']
            )
        ),
        ];

        return $this->view('', 'fc_add_steam_game', $viewParams);
    }

    /**
     * edit template for steam game
     * @param ParameterBag $params
     * @return mixed
     */
    public function actionEditSteamGame(ParameterBag $params)
    {
        $gameId = $this->filter('game', 'uint');
        $game = $this->getSteamGameRepo()
            ->getUserGameById($params->user_id, $gameId);


        $viewParams = ['game' => $game,
            'field_names' => array_unique(
                array_filter(
                    \XF::options()->fc_steam_game_dropdown['field_name']
                )
            ),
        ];
        return $this->view('', 'fc_add_steam_game', $viewParams);

    }

    /**
     * save process for steam game
     * @param $game
     * @throws \XF\Mvc\Reply\Exception
     */
    public function saveSteamGame($game)
    {
        $attachment = $this->request->getFile('attachment', false, false);

        $input = $this->filter(['name' => 'str',
            'link' => 'str',
            'category' => 'str',
            'price' => 'uint',
            'software' => 'str',
            'os' => 'str',
            'content_pack' => 'str',
            'dlc' => 'str',
            'games' => 'str',
            'game_type' => 'str']);

        $input['user_id'] = \XF::visitor()->user_id;

        if ($attachment) {
            $this->validationCheck($attachment);

            $attachment = $this->uploadAttachment($attachment);
            $input['attachment'] = $attachment;
        }

        $game->bulkSet($input);
        $game->save();

    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     */
    public function actionRemove(ParameterBag $params)
    {
        $gameId = $this->filter('game', 'uint');

        $workShopId = $this->filter('workshop', 'uint');

        if ($this->isPost()) {
            $gameId = $this->filter('game', 'uint');
            $userId = $this->filter('user_id', 'uint');
            $workShopId = $this->filter('workShop', 'uint');

            if ($gameId) {
                $this->getSteamGameRepo()
                    ->getUserGameById($userId, $gameId)
                    ->delete();

                return $this->redirect($this->buildLink('members/' . $userId . '/#steam-game'));

            } elseif ($workShopId) {

                $this->getSteamWorkShopRepo()
                    ->getUserWorkshopByIds($userId, $workShopId)
                    ->delete();

                return $this->redirect($this->buildLink('members/' . $userId . '/#steam-workshop'));
            }

            return $this->redirect($this->getDynamicRedirect());
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
    public function actionSteamWorkShop(ParameterBag $params)
    {
        $permission = $this->getVisitorPermission();

        $userAccess = true;

        $userId = ($params->user_id) ? $params->user_id : $this->filter('user_id', 'uint');

        if (\XF::visitor()->user_id != $userId) {
            $userAccess = $this->checkUserTabAccess($userId);
        }

        if( !$permission || !$userAccess ){
            return $this->noPermission();
        }

        /** @var \ForumCube\SteamIntegeration\ControllerPlugin\GameOverview $overviewPlugin */
        $overviewPlugin = $this->plugin('ForumCube\SteamIntegeration:WorkShopOverview');
        $data = $overviewPlugin->getCoreListData($userId);

        $allWorkShops = $data['steam_workshop']->fetch();

        $user = $this->assertViewableUser($userId);

        $viewParams = [
            'allWorkShops' => $allWorkShops,
            'filters' => $data['filters'],
            'user' => $user
        ];

        return $this->view('', 'fc_steam_workshop', $viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\View
     * adding new steam game in xf side
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionAddSteamWorkShop()
    {
        $workShopId = $this->filter('workshop', 'uint');

        if ($this->isPost() && !$workShopId) {
            $workShop = $this->em()->create('ForumCube\SteamIntegeration:SteamWorkShop');
            $this->saveSteamWorkshop($workShop);
            return $this->redirect($this->buildLink('members/' . \XF::visitor()->user_id . '/#steam-workshop'));

        } elseif ($this->isPost() && $workShopId) {
            $workShop = $this->getSteamWorkShopRepo()
                ->getUserWorkshopByIds(\XF::visitor()->user_id, $workShopId);

            $this->saveSteamWorkshop($workShop);
            return $this->redirect($this->buildLink('members/' . \XF::visitor()->user_id . '/#steam-workshop'));
        }

        return $this->view('', 'fc_add_steam_workshop');
    }

    /**
     * edit template for steam game
     * @param ParameterBag $params
     * @return mixed
     */
    public function actionEditSteamWorkshop(ParameterBag $params)
    {
        $workShopId = $this->filter('workshop', 'uint');
        $workShop = $this->getSteamWorkShopRepo()
            ->getUserWorkshopByIds($params->user_id, $workShopId);

        $viewParams = [
            'workshop' => $workShop,
        ];

        return $this->view('', 'fc_add_steam_workshop', $viewParams);

    }

    protected function saveSteamWorkshop($workShop)
    {
        $attachment = $this->request->getFile('attachment', false, false);

        $input = $this->filter(['name' => 'str',
            'link' => 'str',
            'category' => 'str',
            'price' => 'uint',
            'software' => 'str',
            'game' => 'str']);

        $input['user_id'] = \XF::visitor()->user_id;
        if ($attachment) {
            $this->validationCheck($attachment);

            $attachment = $this->uploadAttachment($attachment);
            $input['attachment'] = $attachment;
        }

        $workShop->bulkSet($input);
        $workShop->save();

        return $this->redirect($this->buildLink('members/' . $input['user_id'] . '/#steam-workshop'));

    }


    /**
     * @return \XF\Mvc\Reply\View
     * added new tab to render data for
     * steam friends
     */
    public function actionSteamFriends(ParameterBag $params)
    {
        $permission = $this->getVisitorPermission();

        $userAccess = true;

        if (\XF::visitor()->user_id != $params->user_id) {
            $userAccess = $this->checkUserTabAccess($params->user_id);
        }

        if( !$permission || !$userAccess ) {
            return $this->noPermission();
        }

        $allFriends = $this->getSteamFriendsRepo()
            ->getAllSteamFriends($params->user_id);

        $users = $this->getUserRepo()->getUsersByNames($allFriends->toArray());

        $user = $this->assertViewableUser($params->user_id);

        $viewParams = [
            'users' => $users,
            'currentUser' => $user
        ];

        $user = $this->assertViewableUser($params->user_id);

        if (isset($user->Profile->connected_accounts['steam']) &&
            $user->Profile->connected_accounts['steam'] && $user->Option->fc_steam_data) {
            $steamApi = new Steam($user);
            $steamFriends = $steamApi->getFriends();
            $viewParams['steamFriends'] = !empty($steamFriends['response']['players']) ? $steamFriends['response']['players'] : [];
        }

        return $this->view('', 'fc_steam_friends', $viewParams);
    }

    public function actionAddSteamFriends()
    {
        if ($this->isPost()) {
            $input = $this->filter(['friend' => 'str']);
            $input['user_id'] = \XF::visitor()->user_id;

            if ($input['friend'] == \XF::visitor()->username) {
                return $this->error(\XF::Phrase('fc_friend_is_visitor'));
            }

            $friend = $this->getSteamFriendsRepo()
                ->checkFriendExist($input['friend']);

            if ($friend) {
                return $this->error(\XF::Phrase('fc_already_exist'));
            }

            $friend = $this->em()->create('ForumCube\SteamIntegeration:SteamFriends');
            $friend->bulkSet($input);
            $friend->save();

            return $this->redirect($this->buildLink('members/' . $input['user_id'] . '/#steam-friends'));

        }

        return $this->view('', 'fc_add_steam_friend');
    }

    public function actionRemoveFriend(ParameterBag $params)
    {
        $username = $this->filter('user', 'str');

        if ($this->isPost()) {

            $username = $this->filter('username', 'str');
            $user = $this->filter('user_id', 'uint');

            $this->getSteamFriendsRepo()
                ->getUserFriendByIds($username, $user)
                ->delete();

            return $this->redirect($this->buildLink('members/' . $user . '/#steam-friends'));
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
    public function uploadAttachment($attachment)
    {
        $fileWithExtension = \XF::$time . '.' . $attachment->getExtension();

        $path = 'data://ForumCube/images/' . $fileWithExtension;

        File::copyFileToAbstractedPath($attachment->getTempFile(), $path);

        return $fileWithExtension;
    }

    /**
     * @param $attachment
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function validationCheck($attachment)
    {
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'];

        if (!$attachment) {
            throw $this->exception($this->notFound(\XF::phrase('fc_required_images')));
        }
        if (!in_array($attachment->getExtension(), $allowedExtensions)) {
            throw $this->exception($this->error(\XF::phrase('fc_required_extentions')));
        }

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

    /**
     * @return false|mixed
     */
    private function getVisitorPermission()
    {
        return \XF::visitor()->hasPermission( "fcSteam",
            "fcSteamContent"
        );
    }

    /**
     * check the user access to view
     * tabs by user id
     * @param $userId
     * @return bool
     */
    private function checkUserTabAccess($userId)
    {
        $data = $this->getSteamTabPrivacyRepo()
            ->checkUserAccess($userId);

        return ($data) ? true : false;
    }


}
