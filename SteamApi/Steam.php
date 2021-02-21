<?php

namespace ForumCube\SteamIntegeration\SteamApi;

class Steam
{
    private $userPath = 'http://api.steampowered.com/ISteamUser/';
    private $playerServices = 'https://api.steampowered.com/IPlayerService/';
    private $key = '';
    private $steamId = '';

    public function __construct( $user )
    {
        $record = \XF::em()->find('XF:ConnectedAccountProvider','steam');
        $this->key = $record->options['client_secret'];

        $this->steamId = $user->Profile->connected_accounts['steam'];

    }

    /**
     * @return array|bool|int|mixed|object|\stdClass|string|null
     * getting steam friends for the current user
     */
    public function getFriends()
    {
        $url = $this->userPath."GetFriendList/v0001/?key=$this->key&steamid=$this->steamId&relationship=friend";
        $response = $this->request('GET',$url,[]);
        $friendsList = json_decode($response,true);

        foreach ( $friendsList['friendslist']['friends'] as $friends )
        {
            $steamsIds[] = $friends['steamid'];
        }

        $steamsIds = !empty($steamsIds) ? implode(',',$steamsIds) : [];


        $friends = !empty($steamsIds) ? $this->getusers( $steamsIds ) : [];

        return $friends;
    }

    /**
     * @param $steamsIds
     * @return array|bool|int|mixed|object|\stdClass|string|null
     * return user details against ids
     */
    public function getUsers( $steamsIds )
    {
        $url = $this->userPath."GetPlayerSummaries/v0002/?key=$this->key&steamids=$steamsIds";
        $response = $this->request('GET',$url,[]);

        return json_decode($response,true);
    }

    /**
     * @return array|bool|string
     * fetch steam games for the user
     */
    public function getGames()
    {
        $url = $this->playerServices."GetOwnedGames/v0001/?key=$this->key&steamid=$this->steamId&format=json&include_appinfo=true&include_played_free_games=true";

        $response = $this->request('GET',$url,[]);
        $response = json_decode($response,true);

        return !empty($response['response']) ? $response['response']['games'] : [];
    }



    private function request($method,$url,$headers)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 50,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}