<?php

namespace App\Message\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

class PushBulletApi implements Api
{
    private HttpClientInterface $client;

    private RouterInterface $router;

    #[Required]
    public function setRouter(RouterInterface $router): self
    {
        $this->router = $router;
        return $this;
    }

    #[Required]
    public function setClient(HttpClientInterface $pushBullet): self
    {
        $this->client = $pushBullet;
        return $this;
    }

    public function getDevices(): array
    {
        return $this->client->request(
            Request::METHOD_GET,
            $this->router->generate('api_push_bullet_get_devices')
        )->toArray();
    }

    public function getCurrentUser(): array
    {
        return $this->client->request(
            Request::METHOD_GET,
            $this->router->generate('api_push_bullet_current_user')
        )->toArray();
    }

    public function send(string $phoneNumber, string $message): void
    {
        $sourceIden = $this->getDevices()['devices'][0]['iden'];
        $this->client->request(
            Request::METHOD_POST,
            $this->router->generate('api_push_bullet_send_message'),
            [
                'json' => [
                    'data' => [
                        'addresses' => [$phoneNumber],
                        'guid' => uniqid(),
                        'message' => $message,
                        'target_device_iden' => $sourceIden,
                    ],
                ],
            ]
        )->toArray(false);
    }
}
