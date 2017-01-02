<?php

namespace GDCE\LaravelDreamfactory;

/*
 * Laravel Dreamfactory
 */


use GuzzleHttp\Client;

class LaravelDreamfactory
{
    protected $authUserSession = null;

    private $rememberMe = true;
    private $timeout = 5.0;
    private $dfConnection = [];

    public function __construct()
    {

    }

    public function connect($dfConnectionString = 'default', $authUri = ''){
        $this->dfConnection['base_uri'] = config('laravel-df')[$dfConnectionString]['base_uri'];
        $this->dfConnection['auth_uri'] = config('laravel-df')[$dfConnectionString]['auth_uri'];
        $this->dfConnection['api_key'] = config('laravel-df')[$dfConnectionString]['api_key'];
        $this->dfConnection['accept'] = config('laravel-df')[$dfConnectionString]['accept'];
        $this->dfConnection['email'] = config('laravel-df')[$dfConnectionString]['email'];
        $this->dfConnection['password'] = config('laravel-df')[$dfConnectionString]['password'];
        $this->authenticateDreamfactory($authUri);

    }

    private function authenticateDreamfactory($authUri){
        //Setting up basic connection properties
        $client = new Client([
            'base_uri' => $this->dfConnection['base_uri'].$this->dfConnection['auth_uri'],
            'timeout'  => $this->timeout,
            'headers'  => [
                'Accept'=> $this->dfConnection['accept'],
                'X-DreamFactory-Api-Key'=> $this->dfConnection['api_key']
            ]
        ]);
        $data = [
            "email"=>$this->dfConnection['email'],
            "password"=>$this->dfConnection['password'],
            "remember_me"=>$this->rememberMe
        ];
        $this->authUserSession = json_decode((string) $client->post('',['body'=>json_encode($data)])->getBody());
        dd($this->authUserSession);
    }

    public function store($appUri, $data){
        $client =  new Client([
            'base_uri' => $this->dfConnection['base_uri'].$appUri,
            'timeout'  => $this->timeout,
            'headers'  => [
                'Accept'=> $this->dfConnection['accept'],
                'X-DreamFactory-Api-Key'=> $this->dfConnection['api_key'],
                'X-DreamFactory-Session-Token'=> $this->authUserSession->session_id
            ]
        ]);
        try{
            return json_decode((string) $client->post('',['body'=>json_encode($data)])->getBody(), true)['resource'][0];
        }catch (\Exception $e){
            return false;
        }
    }

}
