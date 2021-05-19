<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransport implements TransportInterface, SetupableTransportInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PhpSerializer|SerializerInterface
     */
    private $serializer;

    /**
     * @var PubSubReceiver
     */
    private $receiver;

    /**
     * @var PubSubSender
     */
    private $sender;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    public function setup(): void
    {
        $this->connection->setup();
    }

    public function get(): iterable
    {
        return ($this->receiver ?? $this->getReceiver())->get();
    }

    private function getReceiver(): PubSubReceiver
    {
        return $this->receiver = new PubSubReceiver($this->connection, $this->serializer);
    }

    public function ack(Envelope $envelope): void
    {
        ($this->receiver ?? $this->getReceiver())->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        ($this->receiver ?? $this->getReceiver())->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return ($this->sender ?? $this->getSender())->send($envelope);
    }

    private function getSender(): PubSubSender
    {
        return $this->sender = new PubSubSender($this->connection, $this->serializer);
    }
}
