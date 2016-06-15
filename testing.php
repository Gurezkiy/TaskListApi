<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 10.06.2016
 * Time: 20:46
 */
require __DIR__.'/vendor/autoload.php';
use GuzzleHttp\Client;
$client = new Client([
    'base_url' => 'http://localhost:8000/',
    'defaults' => [
        'headers' => ['Authorization' => 'Bearer MzYyNjJiYTFmYjM2YjAxMmJkNzQyZWUxNmE1YzEyZGY0YTY5YTNlN2I3NTIzZjFmODc5ZTc1NzVhOTc2NmI0NA'],
        'exceptions' => false
    ]
]);
    //авторизация

   //$response = $client->get('oauth/v2/token?client_id=13_itp2b6b2rg0sksgkw4wo0kogk0o8csskkwcsk4g4kso0wgsg&client_secret=3p2yc18jflic8osgsogcwow0c8k884c00gs08c0wos8k8kk8gk&grant_type=password&username=gurezkiy&password=1234');
    //запрос
 /*   $client = new Client([
        'base_url' => 'http://localhost:8000/',
        'defaults' => [
            'headers' => ['Authorization' => 'Bearer M2YxODVmMGNkOTRkMWUwNjU4YWM1MDA0NTNlZjBkMDFiZTk1ZmM2OTQ4M2I3YjgyMDA1MzRjMTJjOGVkNmYxYw'],
            'exceptions' => false
        ]
    ]);
    $response = $client->get('api/users');*/
    //регистрация
    //$req = $client->createRequest('POST', 'users', ['json' => ["name"=>"gurezkiy","email"=>"gurezkiy@gmail.com","password"=>"1234"]]);
    //$response = $client->send($req);
    //$response = $client->get('api/groups/1');
    // $req = $client->createRequest('POST', 'users', ['json' => ['name' => 'gurezkiy',"email"=>"grezkiy@gmail.com","password"=>"12345678"]]);
    //$response = $client->send($req);
    //$response = $client->delete("api/tasklist/13");
    //$req = $client->createRequest('POST', 'api/privilegies', ['json' => ["taskListId"=>"12","level"=>"2","id"=>"1"]]);
    //$response = $client->send($req);
    $req = $client->createRequest('POST', 'api/groups/1', ['json' => ["userId"=>"1"]]);
    $response = $client->send($req);
    //$response = $client->get('api/privileges?taskListId=12&id=8');
echo "<br>";
echo $response;
