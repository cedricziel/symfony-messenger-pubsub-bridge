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

    public function testCanBeConstructedWithEmptyHost()
    {
        $connection = Connection::fromDsn('pubsub://auto/my-topic?subscription=foo');

        $clientConfig = $connection->getClientConfig();

        self::assertEquals('auto', $clientConfig['projectId']);
    }

    public function testDSNCanBeOverridenByOptions()
    {
        $connection = Connection::fromDsn('pubsub://auto/my-topic?subscription=foo', [
            'client' => [
                'projectId' => 'my-other-project',
            ]
        ]);

        $clientConfig = $connection->getClientConfig();

        self::assertEquals('my-other-project', $clientConfig['projectId']);
    }
}
