<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;

class Connection
{
    private array $clientConfig;

    private array $subscriptionConfig;

    private array $topicConfig;

    public function __construct(array $clientConfig, array $subscriptionConfig, array $topicOptions)
    {
        $this->clientConfig = $clientConfig;
        $this->topicConfig = $topicOptions;
        $this->subscriptionConfig = $subscriptionConfig;
    }

    public static function fromDsn(string $dsn, array $options = []): self
    {
        if (false === $parsedUrl = parse_url($dsn)) {
            // this is a valid URI that parse_url cannot handle when you want to pass all parameters as options
            if ($dsn !== PubSubTransportFactory::GOOGLE_CLOUD_PUBSUB_PROTO_SCHEME) {
                throw new InvalidArgumentException(sprintf('The given PubSub DSN "%s" is invalid.', $dsn));
            }

            $parsedUrl = [];
        }

        $clientOptions = [
            'projectId' => $parsedUrl['host'] ?? null,
        ];

        $pathParts = isset($parsedUrl['path']) ? explode('/', trim($parsedUrl['path'], '/')) : [];
        $topicName = $pathParts[1] ?? '';
        if ($topicName === '') {
            throw new InvalidArgumentException('You need to supply a topic name');
        }

        parse_str($parsedUrl['query'] ?? '', $parsedQuery);

        $topicOptions = [
            'name' => $parsedQuery['topic'] ?? null,
        ];

        $subscriptionConfig = [
            'name' => $parsedQuery['subscription'] ?? null,
        ];

        return new self($clientOptions, $subscriptionConfig, $topicOptions);
    }

    public function publish(string $body, array $headers = []): void
    {
        $this->publishOnTopic(
            $this->topic(),
            $body,
            $headers
        );
    }

    private function publishOnTopic(Topic $topic, string $body, array $headers)
    {
        $topic->publish(new Message([
            'attributes' => $headers,
            'data' => $body,
        ]));
    }

    private function topic(): Topic
    {
        $pubSub = new PubSubClient($this->clientConfig);

        return $pubSub->topic($this->topicConfig['name']);
    }

    public function get(): ?Message
    {
        $pubSub = new PubSubClient($this->clientConfig);

        $subscription = $pubSub->subscription($this->subscriptionConfig['name'], $this->topicConfig['name']);

        $messages = $subscription->pull(['maxMessages' => 1]);

        return $messages[0] ?? null;
    }

    public function ack(Message $message, Subscription $subscription): void
    {
        $subscription->acknowledge($message);
    }

    public function setup()
    {
        $pubSub = new PubSubClient($this->clientConfig);

        $topicName = $this->topicConfig['name'];
        if (!$pubSub->topic($topicName)->exists()) {
            $pubSub->topic($topicName)->create();
        }

        $subscriptionName = $this->subscriptionConfig['name'];
        if (!$pubSub->subscription($subscriptionName, $topicName)->exists()) {
            $pubSub->subscription($subscriptionName, $topicName)->create();
        }
    }

    public function send($body, $param, int $delay)
    {

    }
}