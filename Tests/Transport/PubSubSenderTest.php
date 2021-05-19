<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Fixtures\DummyMessage;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class PubSubSenderTest extends TestCase
{
    public function testItSendsTheEncodedMessage()
    {
        $envelope = new Envelope(new DummyMessage('Oy'));
        $encoded = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('encode')->with($envelope)->willReturnOnConsecutiveCalls($encoded);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('publish')->with($encoded['body'], $encoded['headers'])->willReturn([123]);

        $sender = new PubSubSender($connection, $serializer);
        $sender->send($envelope);
    }

    public function testItSendsTheEncodedMessageWithoutHeaders()
    {
        $envelope = new Envelope(new DummyMessage('Oy'));
        $encoded = ['body' => '...'];

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('encode')->with($envelope)->willReturnOnConsecutiveCalls($encoded);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('publish')->with($encoded['body'], [])->willReturn([123]);

        $sender = new PubSubSender($connection, $serializer);
        $sender->send($envelope);
    }
}
