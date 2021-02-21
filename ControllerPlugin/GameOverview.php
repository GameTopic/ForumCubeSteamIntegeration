<?php

namespace ForumCube\SteamIntegeration\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;

class GameOverview extends AbstractPlugin
{
    public function getCoreListData($userId)
    {
        $steamGameRepo = $this->getSteamGameRepo();
        $steamGameFinder = $steamGameRepo->getAllGames($userId);

        $filters = $this->getResourceFilterInput();

        $this->applyResourceFilters($steamGameFinder, $filters);

        $params = [
            'filters' => $filters,
            'steam_games' => $steamGameFinder
        ];
        return $params;
    }

    public function applyResourceFilters($steamGameFinder, array $filters)
    {

        if (!empty($filters['os'])) {
            $steamGameFinder->where('os', $filters['os']);
        }

        if (!empty($filters['type'])) {
            switch ($filters['type']) {
                case 'free':
                    $steamGameFinder->where('price', 0);
                    break;

                case 'paid':
                    $steamGameFinder->where('price', '>', 0);
                    break;
            }
        }

        if (!empty($filters['games'])) {
            $steamGameFinder->where('games', $filters['games']);
        }

        if (!empty($filters['dlc'])) {
            $steamGameFinder->where('dlc', $filters['dlc']);
        }

        if (!empty($filters['content_pack'])) {
            $steamGameFinder->where('content_pack', $filters['content_pack']);
        }

        if (!empty($filters['software'])) {
            $steamGameFinder->where('software', $filters['software']);
        }

        if (!empty($filters['software'])) {
            $steamGameFinder->where('software', $filters['software']);
        }

        if (!empty($filters['category'])) {
            $steamGameFinder->where('category', $filters['category']);
        }

        if (!empty($filters['game_type'])) {
            $steamGameFinder->where('game_type', $filters['game_type']);
        }

        if (!empty($filters['name'])) {
            $steamGameFinder->where('name', $filters['name']);
        }

    }

    public function actionFilters(\ForumCube\SteamIntegeration\Entity\SteamGame $game = null, $userId)
    {
        $filters = $this->getResourceFilterInput();
        $user = $this->filter('user_id', 'uint');

        if ($this->filter('apply', 'bool')) {

            return $this->redirect($this->buildLink(
                    $game ? 'members/' : 'members/' . $user,
                    $user,
                    $filters

                ) . '#steam-game');
        }


        $viewParams = [
            'user_id' => $userId,
            'field_names' => array_unique(
                array_filter(
                    \XF::options()->fc_steam_game_dropdown['field_name']
                )
            ),];

        return $this->view('', 'fc_steam_game_filters', $viewParams);
    }

    public function getResourceFilterInput()
    {
        $filters = [];

        $input = $this->filter([
            'os' => 'str',
            'games' => 'str',
            'dlc' => 'str',
            'content_pack' => 'str',
            'type' => 'str',
            'software' => 'str',
            'category' => 'str',
            'game_type' => 'str',
            'name' => 'str'
        ]);

        if ($input['os']) {
            $filters['os'] = $input['os'];
        }

        if ($input['type'] && ($input['type'] == 'free' || $input['type'] == 'paid')) {
            $filters['type'] = $input['type'];
        }

        if ($input['games']) {
            $filters['games'] = $input['games'];
        }

        if ($input['dlc']) {
            $filters['dlc'] = $input['dlc'];
        }

        if ($input['software']) {
            $filters['software'] = $input['software'];
        }

        if ($input['category']) {
            $filters['category'] = $input['category'];
        }

        if ($input['game_type']) {
            $filters['game_type'] = $input['game_type'];
        }

        if ($input['name']) {
            $filters['name'] = $input['name'];
        }

if ($input['content_pack']) {
            $filters['content_pack'] = $input['content_pack'];
        }
        return $filters;
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getSteamGameRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamGames');
    }
}
