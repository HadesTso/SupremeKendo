<?php

namespace App\Service;

use GuzzleHttp\Client;

class APIHelperService
{
    public function post($body, $apiStr)
    {
        $client = new Client();
        $res = $client->request('POST', $apiStr,
            [
                'json' => $body,
                'headers' => [
                    'Content-type'=> 'application/json',
                    "Accept"=>"application/json"
                ]
            ]);

        return $res->getBody()->getContents();
    }

    public function get($apiStr,$header)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://192.168.31.XX:xxx/api/']);
        $res = $client->request('GET', $apiStr,['headers' => $header]);
        $statusCode= $res->getStatusCode();

        $header= $res->getHeader('content-type');

        $data = $res->getBody();

        return $data;
    }
}
