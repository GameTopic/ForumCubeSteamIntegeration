<?php

namespace ForumCube\SteamIntegeration\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;

class WorkShopOverview extends AbstractPlugin
{
    public function getCoreListData($userId)
    {
        $steamWorkShopRepo = $this->getSteamWorkShopRepo();
        $steamWorkShopFinder = $steamWorkShopRepo->getAllWorkShops($userId);

        $filters = $this->getResourceFilterInput();

        $this->applyResourceFilters($steamWorkShopFinder, $filters);

        $params = [
            'filters' => $filters,
            'steam_workshop' => $steamWorkShopFinder
        ];
        return $params;
    }

    public function applyResourceFilters($steamWorkShopFinder, array $filters)
    {

        if (!empty($filters['type'])) {
            switch ($filters['type']) {
                case 'free':
                    $steamWorkShopFinder->where('price', 0);
                    break;

                case 'paid':
                    $steamWorkShopFinder->where('price', '>', 0);
                    break;
            }
        }

        if (!empty($filters['game'])) {
            $steamWorkShopFinder->where('game', $filters['game']);
        }

        if (!empty($filters['software'])) {
            $steamWorkShopFinder->where('software', $filters['software']);
        }

        if (!empty($filters['category'])) {
            $steamWorkShopFinder->where('category', $filters['category']);
        }

        if (!empty($filters['name'])) {
            $steamWorkShopFinder->where('name', $filters['name']);
        }

    }

    public function actionFilters(\ForumCube\SteamIntegeration\Entity\SteamWorkShop $workShop = null, $userId)
    {
        $filters = $this->getResourceFilterInput();
        $user = $this->filter('user_id', 'uint');

        if ($this->filter('apply', 'bool')) {

            return $this->redirect($this->buildLink(
                    $workShop ? 'members/' : 'members/' . $user,
                    $user,
                    $filters

                ) . '#steam-workshop');
        }


        $viewParams = [
            'user_id' => $userId,
            ];

        return $this->view('', 'fc_steam_workshop_filters', $viewParams);
    }

    public function getResourceFilterInput()
    {
        $filters = [];

        $input = $this->filter([
            'game' => 'str',
            'type' => 'str',
            'software' => 'str',
            'category' => 'str',
            'name' => 'str'
        ]);

        if ($input['type'] && ($input['type'] == 'free' || $input['type'] == 'paid')) {
            $filters['type'] = $input['type'];
        }

        if ($input['game']) {
            $filters['game'] = $input['game'];
        }

        if ($input['software']) {
            $filters['software'] = $input['software'];
        }

        if ($input['category']) {
            $filters['category'] = $input['category'];
        }

        if ($input['name']) {
            $filters['name'] = $input['name'];
        }

        return $filters;
    }

    /**
     * @return \XF\Mvc\Entity\Repository
     */
    private function getSteamWorkShopRepo()
    {
        return $this->repository('ForumCube\SteamIntegeration:SteamWorkShop');
    }
}