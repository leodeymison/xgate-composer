<?php
 
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ResponseHttp {
    public mixed $data;
    
    public function __construct(mixed $data)
    {
        $this->data = $data;
    }
}

class Http {
    public string $url;
    private Client $client;

    public function __construct(string $url) {
        $this->url = $url;
        $this->client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 10.0,
        ]);
    }

    public function get(string $endpoint, array|null $header = null): ResponseHttp {
        $response = $this->client->request('GET', $endpoint, [
            'headers' => (array) $header
        ]);

        $body = json_decode($response->getBody()->getContents());
        return new ResponseHttp($body);

    }

    public function post(string $endpoint, mixed $body, array|null $header = null): ResponseHttp {
        $response = $this->client->request('POST', $endpoint, [
            'headers' => (array) $header,
            'json'    => $body
        ]);

        $body = json_decode($response->getBody()->getContents());
        return new ResponseHttp($body);

    }
}