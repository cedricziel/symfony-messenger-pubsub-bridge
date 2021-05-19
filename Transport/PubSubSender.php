<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Topic;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class PubSubSender implements SenderInterface
{
    /**
     * @var PhpSerializer|SerializerInterface
     */
    private $serializer;

    private Connection $connection;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        /** @var DelayStamp|null $delayStamp */
        $delayStamp = $envelope->last(DelayStamp::class);
        $delay = null !== $delayStamp ? $delayStamp->getDelay() : 0;

        try {
            $this->connection->publish(new Message($encodedMessage['body']));
            $id = $this->connection->send($encodedMessage['body'], $encodedMessage['headers'] ?? [], $delay);
        } catch (\Exception $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        return $envelope->with(new TransportMessageIdStamp($id));
    }
}
