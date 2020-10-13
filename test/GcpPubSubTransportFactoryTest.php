<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Test;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\GcpPubSubTransport;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\GcpPubSubTransportFactory;
use Google\Cloud\PubSub\PubSubClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

class GcpPubSubTransportFactoryTest extends TestCase
{
    /**
     * @dataProvider provideDSNs
     */
    public function testCanCreateTransportFromDSN($dsn, $options, $expected)
    {
        $client = $this->getMockBuilder(PubSubClient::class)->disableOriginalConstructor()->getMock();
        $factory = new GcpPubSubTransportFactory($client);
        $serializer = Serializer::create();

        $transport = $factory->createTransport($dsn, $options, $serializer);
        if ($expected) {
            self::assertInstanceOf(GcpPubSubTransport::class, $transport);
        }
    }

    /**
     * @dataProvider provideDSNs
     */
    public function testCanSignalSupportForGcpsSchema($dsn, $options, $expected)
    {
        $client = $this->getMockBuilder(PubSubClient::class)->disableOriginalConstructor()->getMock();
        $factory = new GcpPubSubTransportFactory($client);

        self::assertEquals($factory->supports($dsn, $options), $expected);
    }

    public function provideDSNs()
    {
        return [
            ['', [], false],
            ['gcpps://', [], true],
            ['gcpps://my-gcp-project', [], true],
            ['amqp://localhost', [], false],
        ];
    }
}
