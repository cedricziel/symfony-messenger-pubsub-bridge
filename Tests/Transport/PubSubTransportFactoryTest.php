<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransport;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class PubSubTransportFactoryTest extends TestCase
{
    public function testSupportsOnlyAmqpTransports()
    {
        $factory = new PubSubTransportFactory();

        self::assertTrue($factory->supports('pubsub://my-project/my-topic?subscription=foo', []));
        self::assertFalse($factory->supports('sqs://localhost', []));
        self::assertFalse($factory->supports('invalid-dsn', []));
    }

    public function testItCreatesTheTransport()
    {
        $factory = new PubSubTransportFactory();
        $serializer = $this->createMock(SerializerInterface::class);

        $expectedTransport = new PubSubTransport(Connection::fromDsn('pubsub://my-project/my-topic?subscription=foo', ['host' => 'localhost']), $serializer);

        self::assertEquals($expectedTransport, $factory->createTransport('pubsub://my-project/my-topic?subscription=foo', ['host' => 'localhost'], $serializer));
    }
}
