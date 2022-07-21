<?php

namespace App\Message\API;

interface Api
{
    public function send(string $phoneNumber, string $message): void;
}