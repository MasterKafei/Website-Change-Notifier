<?php

namespace App\Message\Sender;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ContentChangeSender extends AbstractSender
{
    public function notifyContentChange(string $phoneNumber): void
    {
        parent::send($phoneNumber, "Website notifier : Le contenu de la page a changé");
    }
}
