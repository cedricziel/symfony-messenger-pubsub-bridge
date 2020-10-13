<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub;

use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class GcpPubSubTransportFactory implements TransportFactoryInterface
{
    const GOOGLE_CLOUD_PUBSUB_SCHEME = 'gcpps';

    /**
     * @var PubSubClient
     */
    private PubSubClient $client;

    public function __construct(PubSubClient $client)
    {
        $this->client = $client;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new GcpPubSubTransport();
    }

    public function supports(string $dsn, array $options): bool
    {
        if (strpos($dsn, self::GOOGLE_CLOUD_PUBSUB_SCHEME) === 0) {
            return true;
        }

        return false;
    }
}
