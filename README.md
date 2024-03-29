# Symfony Messenger Bridge for Google Cloud Pub/Sub

Bridge [Google Cloud Pub/Sub](https://cloud.google.com/pubsub) with the Symfony messenger component.

**Note:** Use [cedricziel/symfony-messenger-pubsub-bundle](https://github.com/cedricziel/symfony-messenger-pubsub-bundle) for automatic wiring in your Symfony Framework application.

## Installation

```shell
composer require cedricziel/messenger-pubsub
```

## Usage

To use the `pubsub` transport, you would register the `PubSubTransportFactory` through the following configuration:

```yaml
# config/services.yaml
services:
    CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransportFactory:
        tags: [messenger.transport_factory]
```

Create a concrete transport:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            my-pubsub: 'pubsub://...'
```

## Notable

Google Cloud Pub/Sub does not support delaying messages, so a `DelayStamp` will have no effect.

## License

MIT
