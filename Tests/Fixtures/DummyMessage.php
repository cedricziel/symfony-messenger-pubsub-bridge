<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Fixtures;

class DummyMessage
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
