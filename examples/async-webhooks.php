<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\Contracts\{WebhookHandlerInterface, WebhookQueueInterface};
use Dropshipping\DS;
use Dropshipping\DTO\Webhooks\WebhookEventInterface;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Webhook\WebhookMessage;

// --- In-memory queue (replace with Redis, RabbitMQ, database, etc.) ---
class InMemoryWebhookQueue implements WebhookQueueInterface
{
    /** @var WebhookMessage[] */
    private array $messages = [];

    public function push(WebhookMessage $message): void
    {
        $this->messages[] = $message;
    }

    public function pop(): ?WebhookMessage
    {
        return array_shift($this->messages);
    }
}

$queue = new InMemoryWebhookQueue();

// --- HTTP controller: enqueue the incoming webhook immediately ---
DS::queueWebhookDispatcher($queue)->dispatch(DS::incomingWebhook());
echo "Webhook enqueued.\n";

// --- Background worker: process queued messages ---
$dispatcher = DS::webhookDispatcher(DS::webhookPipeline($config->getWebhookSignatureSecret()));

$dispatcher->registerHandler(new class implements WebhookHandlerInterface {
    public function supports(WebhookEventInterface $event): bool
    {
        return $event->getEventType() === WebhookEventType::DeliveryShipment;
    }

    public function handle(WebhookEventInterface $event): void
    {
        echo "Order {$event->order->id} shipped — tracking: {$event->delivery->trackingCode}\n";
    }
});

$processed = DS::webhookWorker($queue, $dispatcher)->run(maxMessages: 100);

echo "Processed {$processed} webhook(s).\n";
