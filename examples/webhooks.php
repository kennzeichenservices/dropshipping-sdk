<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\DS;
use Dropshipping\DTO\Webhooks\WebhookEventInterface;
use Dropshipping\Enums\WebhookEventType;

// --- Implement a handler ---
class ShipmentHandler implements WebhookHandlerInterface
{
    public function supports(WebhookEventInterface $event): bool
    {
        return $event->getEventType() === WebhookEventType::DeliveryShipment;
    }

    public function handle(WebhookEventInterface $event): void
    {
        echo "Order {$event->order->id} shipped — tracking: {$event->delivery->trackingCode}\n";
    }
}

// --- Wire up pipeline and dispatcher ---
$dispatcher = DS::webhookDispatcher(DS::webhookPipeline($config->getWebhookSignatureSecret()));
$dispatcher->registerHandler(new ShipmentHandler());

// --- Receive a webhook (e.g. in a controller) ---
$dispatcher->dispatch(DS::incomingWebhook());
