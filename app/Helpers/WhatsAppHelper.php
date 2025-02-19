<?php

namespace App\Helpers;

use App\Models\GeneralSettingsModel;

class WhatsAppHelper
{
    protected $apiKey;

    public function __construct()
    {
        $generalSettingsModel = new GeneralSettingsModel();
        $generalSettings = $generalSettingsModel->first();

        // Debugging untuk melihat isi data
        // var_dump($generalSettings);
        // dd($generalSettings);
        // die();

        // Pastikan cara akses sesuai dengan tipe data yang dikembalikan
        if (is_array($generalSettings)) {
            $this->apiKey = $generalSettings['token_api'] ?? null;
        } elseif (is_object($generalSettings)) {
            $this->apiKey = $generalSettings->token_api ?? null;
        } else {
            $this->apiKey = null;
        }

        if (!$this->apiKey) {
            throw new \Exception("Token API tidak ditemukan di database.");
        }
    }

    public function sendMessage($to, $message)
    {
        $url = 'https://api.fonnte.com/send';

        $data = [
            'target' => $to,
            'message' => $message,
        ];

        $headers = [
            "Authorization: {$this->apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
