<?php

namespace Core\tools;


use GuzzleHttp\Client;

class Curl
{
    public static $defaultConfig = ['timeout' => 5];

    public static function Request($method, $uri, $params = [])
    {
        $client = new Client(self::$defaultConfig);
        $response = $client->request($method, $uri, $params);
        $httpCode = $response->getStatusCode();
        if ($httpCode >= 200 && $httpCode < 400) {
            $result['code'] = 1;
            $body = $response->getBody();
            $result['data'] = $body->getContents();
            $result['json'] = json_decode($result['data'], true);
        } else {
            $result['code'] = 0;
            $body = $response->getBody();
            $result['data'] = $body->getContents();
            $result['error'] = $response->getReasonPhrase();
        }
        return $result;
    }
}