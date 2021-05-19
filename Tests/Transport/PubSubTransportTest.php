<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Fixtures\DummyMessage;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransport;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransportTest extends TestCase
{
    public function testItIsATransport()
    {
        $transport = $this->getTransport();

        self::assertInstanceOf(TransportInterface::class, $transport);
    }

    private function getTransport(SerializerInterface $serializer = null, Connection $connection = null): PubSubTransport
    {
        $serializer = $serializer ?? $this->createMock(SerializerInterface::class);
        $connection = $connection ?? $this->createMock(Connection::class);

        return new PubSubTransport($connection, $serializer);
    }

    public function testReceivesMessages()
    {
        $transport = $this->getTransport(
            $serializer = $this->createMock(SerializerInterface::class),
            $connection = $this->createMock(Connection::class)
        );

        $decodedMessage = new DummyMessage('Decoded.');

        $pubSubMessage = $this->createMock(Message::class);
        $pubSubMessage->method('data')->willReturn('body');
        $pubSubMessage->method('attributes')->willReturn(['my' => 'header']);
        $pubSubMessage->method('subscription')->willReturn($this->createMock(Subscription::class));

        $serializer->method('decode')->with([
            'body' => 'body',
            'headers' => ['my' => 'header'],
        ])->willReturn(new Envelope($decodedMessage));
        $connection->method('get')->willReturn($pubSubMessage);

        $envelopes = iterator_to_array($transport->get());
        self::assertSame($decodedMessage, $envelopes[0]->getMessage());
    }
}
