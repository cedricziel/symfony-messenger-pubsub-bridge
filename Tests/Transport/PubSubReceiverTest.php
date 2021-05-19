<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubReceivedStamp;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubReceiver;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Bridge\Amqp\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer as SerializerComponent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PubSubReceiverTest extends TestCase
{
    public function testItReturnsTheDecodedMessageToTheHandler()
    {
        $serializer = new Serializer(
            new SerializerComponent\Serializer([new ObjectNormalizer()], ['json' => new JsonEncoder()])
        );

        $pubSubMessage = $this->createPubSubMessage();
        $connection = $this->createMock(Connection::class);
        $connection->method('get')->willReturn($pubSubMessage);

        $receiver = new PubSubReceiver($connection, $serializer);
        $actualEnvelopes = iterator_to_array($receiver->get());

        self::assertCount(1, $actualEnvelopes);
        self::assertEquals(new DummyMessage('Hi'), $actualEnvelopes[0]->getMessage());
    }

    private function createPubSubMessage(): Message
    {
        $subscription = $this->createMock(Subscription::class);
        $envelope = $this->createMock(Message::class);

        $envelope->method('data')->willReturn('{"message": "Hi"}');
        $envelope->method('attributes')->willReturn([
            'type' => DummyMessage::class,
        ]);
        $envelope->method('subscription')->willReturn($subscription);

        return $envelope;
    }

    public function testItThrowsATransportExceptionIfItCannotAcknowledgeMessage()
    {
        $this->expectException(TransportException::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $pubSubMessage = $this->createPubSubMessage();

        $connection = $this->createMock(Connection::class);
        $connection->method('get')->willReturn($pubSubMessage);
        $connection->method('ack')->with($pubSubMessage)->willThrowException(new TransportException());

        $subscription = $this->createMock(Subscription::class);

        $receiver = new PubSubReceiver($connection, $serializer);

        $receiver->ack(new Envelope(new stdClass(), [new PubSubReceivedStamp($pubSubMessage, $subscription)]));
    }

    public function testItThrowsATransportExceptionIfItCannotRejectMessage()
    {
        $this->expectException(TransportException::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $pubSubMessage = $this->createPubSubMessage();
        $connection = $this->createMock(Connection::class);
        $connection->method('get')->willReturn($pubSubMessage);
        $connection->method('nack')->with($pubSubMessage)->willThrowException(new TransportException());

        $subscription = $this->createMock(Subscription::class);

        $receiver = new PubSubReceiver($connection, $serializer);
        $receiver->reject(new Envelope(new stdClass(), [new PubSubReceivedStamp($pubSubMessage, $subscription)]));
    }
}
