<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Async;

use Dropshipping\Async\QueueWebhookDispatcher;
use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\DS;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Webhook\WebhookMessage;
use PHPUnit\Framework\TestCase;

final class QueueWebhookDispatcherTest extends TestCase
{
    private const SECRET = 'test-secret';

    public function test_dispatch_enqueues_a_message_with_a_valid_signature(): void
    {
        $queue = $this->queue();
        $message = $this->message('{"eventType":"PING"}', self::SECRET);

        $this->dispatcher($queue)->dispatch($message);

        self::assertSame([$message], $queue->pushed);
    }

    public function test_dispatch_rejects_a_message_with_an_invalid_signature(): void
    {
        $queue = $this->queue();

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $this->dispatcher($queue)->dispatch(
            new WebhookMessage('{"eventType":"PING"}', 'forged-signature', 1, '3.1.0'),
        );
    }

    /**
     * The queue must stay untouched when verification fails — otherwise anyone able to
     * reach the public webhook URL could flood the backend with unauthenticated messages.
     */
    public function test_a_forged_message_never_reaches_the_queue(): void
    {
        $queue = $this->queue();

        try {
            $this->dispatcher($queue)->dispatch(
                new WebhookMessage('{"eventType":"PING"}', 'forged-signature', 1, '3.1.0'),
            );
        } catch (WebhookException) {
            // expected — assertion below is the point of this test
        }

        self::assertSame([], $queue->pushed);
    }

    public function test_ds_factory_builds_a_verifying_dispatcher(): void
    {
        $queue = $this->queue();
        $message = $this->message('{"eventType":"PING"}', self::SECRET);

        DS::queueWebhookDispatcher($queue, self::SECRET)->dispatch($message);

        self::assertSame([$message], $queue->pushed);
    }

    public function test_ds_factory_rejects_a_missing_secret_with_a_descriptive_error(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Webhook signature secret must not be empty');

        DS::queueWebhookDispatcher($this->queue(), null);
    }

    private function dispatcher(WebhookQueueInterface $queue): QueueWebhookDispatcher
    {
        return new QueueWebhookDispatcher($queue, new WebhookSignatureVerifier(self::SECRET));
    }

    private function message(string $payload, string $secret): WebhookMessage
    {
        return new WebhookMessage($payload, hash_hmac('sha256', $payload, $secret), 1, '3.1.0');
    }

    private function queue(): RecordingWebhookQueue
    {
        return new RecordingWebhookQueue();
    }
}

/**
 * In-memory queue that keeps every pushed message so tests can assert on what was enqueued.
 */
final class RecordingWebhookQueue implements WebhookQueueInterface
{
    /** @var list<WebhookMessage> */
    public array $pushed = [];

    public function push(WebhookMessage $message): void
    {
        $this->pushed[] = $message;
    }

    public function pop(): ?WebhookMessage
    {
        return array_shift($this->pushed);
    }
}
