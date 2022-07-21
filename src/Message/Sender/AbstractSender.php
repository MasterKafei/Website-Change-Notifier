<?php

namespace App\Message\Sender;

use App\Message\API\Api;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractSender
{
    private Api $api;

    #[Required]
    public function setMessageApi(Api $api): self
    {
        $this->api = $api;
        return $this;
    }

    protected function send(string $phoneNumber, string $message): void
    {
        $this->api->send($phoneNumber, $message);
    }
}
