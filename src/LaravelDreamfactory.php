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
    private $guzzleOption = [];
    private $client = null;
    private $environment = 'production';

    public function __construct($dfConnectionString = 'default')
    {
        $this->connect($dfConnectionString);
    }

    public function connect($dfConnectionString){
        $this->dfConnection['base_uri'] = config('laravel-df')[$dfConnectionString]['base_uri'];
        $this->dfConnection['auth_uri'] = config('laravel-df')[$dfConnectionString]['auth_uri'];
        $this->dfConnection['app_uri'] = config('laravel-df')[$dfConnectionString]['app_uri'];
        $this->dfConnection['api_key'] = config('laravel-df')[$dfConnectionString]['api_key'];
        $this->dfConnection['accept'] = config('laravel-df')[$dfConnectionString]['accept'];
        $this->dfConnection['email'] = config('laravel-df')[$dfConnectionString]['email'];
        $this->dfConnection['password'] = config('laravel-df')[$dfConnectionString]['password'];

        //Environment
        $this->environment = config('laravel-df')['environment'];

        $this->authenticateDreamfactory();

        $this->guzzleOption = [
            'base_uri' => $this->dfConnection['base_uri'].$this->dfConnection['app_uri'],
            'timeout'  => $this->timeout,
            'headers'  => [
                'Accept'=> $this->dfConnection['accept'],
                'X-DreamFactory-Api-Key'=> $this->dfConnection['api_key'],
                'X-DreamFactory-Session-Token' => $this->authUserSession->session_id
            ]
        ];

        $this->client =  new Client($this->guzzleOption);

    }

    private function authenticateDreamfactory(){
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
    }

    public function store($appUri, $data){
        try{
            return json_decode((string) $this->client->post($appUri,['body'=>json_encode($data)])->getBody(), true)['resource'][0];
        }catch (\Exception $e){
            return false;
        }
    }

    public function update($appUri, $id, $data){
        try{
            return json_decode((string) $this->client->patch($appUri.'/'.$id,['body'=>json_encode($data)])->getBody(), true)['resource'][0];
        }catch (\Exception $e){
            return false;
        }
    }

    public function destroy($appUri, $id){
        try{
            return json_decode((string) $this->client->delete($appUri.'/'.$id)->getBody(), true)['resource'][0];
        }catch (\Exception $e){
            return false;
        }
    }

    public function datatables($table, $data, $filters = []){
        $response = $this->client->get($table.$this->datatable_filter($data, $filters));
        $result = json_decode((string) $response->getBody(), true);
        $datatables = [
            'input' => $data,
            'data'  => $result['resource'],
            'recordsFiltered'   =>$result['meta']['count'],
            'recordsTotal'      => $result['meta']['count'],
            'draw'  => $data['draw']
        ];
        //Update search field
        if($this->environment === 'development'){
            $this->alter_search_text($table, $data['columns']);
        }
        return $datatables;
    }

    private function datatable_filter($data, $filters){
        $request = $data;
        $limit = "?limit=".$request['length'];
        $offset = "&offset=".$request['start'];
        $filter = "";
        if(count($filters)>0){
            $filter = "&filter=";
            foreach ($filters as $key => $value){
                $filter.= "(".$key." = ".$value.") and";
            }
            $filter = substr($filter,0,strlen($filter)-4);
        }
        $count = "&include_count=true";
        $fields = "&fields=";
        for($i=0;$i<count($request['columns']);$i++){
            if($request['columns'][$i]['data']!='actions'){
                $fields.=$request['columns'][$i]['data'].',';
            }
        }
        $fields = substr($fields,0,strlen($fields)-1);
        $search = "";
        if(trim($request['search']['value']) != null){
            $search = " and (search_text like %".strtolower(trim($request['search']['value']))."%)";
        }
        $order = "&order=".$request['columns'][$request['order'][0]['column']]['data']." ".strtoupper($request['order'][0]['dir']);
        return $limit.$offset.$filter.$search.$count.$fields.$order;
    }

    public function alter_search_text($table, $columns){
        $fields = $columns;
        $search_text_db_funtion = "lower(concat(";
        for($i=0;$i<count($fields);$i++){
            if(!in_array($fields[$i]['data'],['actions','id','created_at','updated_at'])){
                $search_text_db_funtion .= $fields[$i]['data'].",';',";
            }
        }
        $search_text_db_funtion = substr($search_text_db_funtion,0,strlen($search_text_db_funtion)-5);
        $search_text_db_funtion .="))";
        $body = '{
          "alias": "search_text",
          "name": "search_text",
          "label": "Search Text",
          "description": null,
          "type": "virtual",
          "db_type": null,
          "length": null,
          "precision": null,
          "scale": null,
          "default": null,
          "required": false,
          "allow_null": true,
          "fixed_length": false,
          "supports_multibyte": false,
          "auto_increment": false,
          "is_primary_key": false,
          "is_unique": false,
          "is_index": false,
          "is_foreign_key": false,
          "is_virtual_foreign_key": false,
          "is_foreign_ref_service": false,
          "ref_service": null,
          "ref_service_id": null,
          "ref_table": null,
          "ref_fields": null,
          "ref_on_update": null,
          "ref_on_delete": null,
          "picklist": null,
          "validation": null,
          "db_function": {
            "function": "'.$search_text_db_funtion.'"
          }
        }';
        $schema = str_replace('_table/','_schema/', $table);
        $this->client->patch($schema.'/search_text', ['body'=>$body]);

    }

}
