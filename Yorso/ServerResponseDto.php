<?php

class ServerResponseDto
{
    public $code;
    public $body;
    public $headers;

    public function isOk(): bool
    {
        return $this->code == 200;
    }

    public function isCreated(): bool
    {
        return $this->code == 201;
    }

    public function isRedirected(): bool
    {
        return $this->code == 302;
    }
}
