<?php

namespace App\Libraries\enums;

use Config\Fonnte;
use GuzzleHttp\Client;

class FonnteLibrary
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $config = new Fonnte();
        $this->apiKey = $config->apiKey;
        $this->apiUrl = $config->apiUrl;
        $this->client = new Client();
    }

    public function sendMessage($number, $message)
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'form_params' => [
                    'target' => $number,
                    'message' => $message,
                    'delay' => 1,
                ],
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

