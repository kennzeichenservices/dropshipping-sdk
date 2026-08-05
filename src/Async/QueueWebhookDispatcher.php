<?php

declare(strict_types=1);

namespace Dropshipping\Async;

use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Webhook\WebhookMessage;

/**
 * Asynchronous webhook dispatcher that enqueues messages for later processing.
 *
 * Instead of processing a webhook message immediately, this dispatcher
 * pushes it onto a {@see WebhookQueueInterface} queue so that a
 * {@see \Dropshipping\Async\WebhookWorker} can handle it asynchronously.
 *
 * The signature is verified *before* the message reaches the queue. The worker-side
 * pipeline verifies again, but that happens too late to protect the queue itself: an
 * unauthenticated enqueue lets anyone who can reach the public webhook URL flood the
 * backend, and leaves the HTTP endpoint unable to answer 401 for a forged payload.
 */
final class QueueWebhookDispatcher
{
    /**
     * @param WebhookQueueInterface    $queue    Queue implementation to push messages onto.
     * @param WebhookSignatureVerifier $verifier Verifier applied before a message is enqueued.
     */
    public function __construct(
        private readonly WebhookQueueInterface $queue,
        private readonly WebhookSignatureVerifier $verifier,
    ) {
    }

    /**
     * Verify the message signature and enqueue it for asynchronous processing.
     *
     * @param WebhookMessage $message The webhook message to enqueue.
     *
     * @throws WebhookException If the signature is invalid. The message is not enqueued;
     *                          answer the HTTP request with 401 in that case.
     */
    public function dispatch(WebhookMessage $message): void
    {
        $this->verifier->verify($message->getPayload(), $message->getSignature());

        $this->queue->push($message);
    }
}
