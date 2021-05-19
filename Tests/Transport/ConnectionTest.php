<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testItCannotBeConstructedWithAWrongDsn()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given PubSub DSN "pubsub://:" is invalid.');

        Connection::fromDsn('pubsub://:');
    }
}
