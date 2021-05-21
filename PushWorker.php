<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\RejectRedeliveredMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class PushWorker
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(MessageBusInterface $bus, EventDispatcherInterface $eventDispatcher = null, LoggerInterface $logger = null) {
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function work(Envelope $envelope, string $transportName): void
    {
        $event = new WorkerMessageReceivedEvent($envelope, $transportName);
        $this->dispatchEvent($event);
        $envelope = $event->getEnvelope();

        if (!$event->shouldHandle()) {
            throw new BadRequestHttpException('No handler');
        }

        try {
            $envelope = $this->bus->dispatch($envelope->with(new ReceivedStamp($transportName), new ConsumedByWorkerStamp()));
        } catch (Throwable $throwable) {
            $rejectFirst = $throwable instanceof RejectRedeliveredMessageException;
            if ($rejectFirst) {
                // redelivered messages are rejected first so that continuous failures in an event listener or while
                // publishing for retry does not cause infinite redelivery loops
                throw new BadRequestHttpException('Reject');
            }

            if ($throwable instanceof HandlerFailedException) {
                $envelope = $throwable->getEnvelope();
            }

            $failedEvent = new WorkerMessageFailedEvent($envelope, $transportName, $throwable);
            $this->dispatchEvent($failedEvent);

            if (!$rejectFirst) {
                throw new BadRequestHttpException('Reject');
            }

            return;
        }

        $handledEvent = new WorkerMessageHandledEvent($envelope, $transportName);
        $this->dispatchEvent($handledEvent);
        $envelope = $handledEvent->getEnvelope();

        if (null !== $this->logger) {
            $message = $envelope->getMessage();
            $context = [
                'message' => $message,
                'class' => get_class($message),
            ];
            $this->logger->info('{class} was handled successfully (acknowledging to transport).', $context);
        }

        // push messages are not ack-ed
    }

    private function dispatchEvent(object $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
