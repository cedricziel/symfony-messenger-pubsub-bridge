<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransportFactory implements TransportFactoryInterface
{
    const GOOGLE_CLOUD_PUBSUB_SCHEME = 'pubsub';
    const GOOGLE_CLOUD_PUBSUB_PROTO_SCHEME = 'pubsub://';

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        if (!$this->supports($dsn, $options)) {
            throw new InvalidArgumentException(sprintf('Invalid DSN: %s', self::GOOGLE_CLOUD_PUBSUB_SCHEME));
        }

        return new PubSubTransport(Connection::fromDsn($dsn, $options), $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        if (strpos($dsn, self::GOOGLE_CLOUD_PUBSUB_SCHEME) === 0) {
            return true;
        }

        return false;
    }
}
