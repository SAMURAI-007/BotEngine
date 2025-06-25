<?php

namespace Bot;

class Bot
{
    private $apiToken;

    public function __construct($apiToken)
    {
        $this->apiToken = $apiToken;
    }

    public function sendMessage($chatId, $message)
    {
        $url = "https://api.telegram.org/bot{$this->apiToken}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        $this->makeRequest($url, $data);
    }

    public function receiveUpdates()
    {
        $url = "https://api.telegram.org/bot{$this->apiToken}/getUpdates";
        return $this->makeRequest($url);
    }

    private function makeRequest($url, $data = [])
    {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context  = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }
}