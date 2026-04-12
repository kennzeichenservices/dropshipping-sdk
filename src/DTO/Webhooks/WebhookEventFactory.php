<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Exceptions\WebhookException;

/**
 * Factory that creates the appropriate WebhookEventInterface implementation
 * from a raw webhook payload array.
 *
 * Determines the event type from the payload and delegates instantiation
 * to the corresponding event class.
 */
final class WebhookEventFactory
{
    /**
     * Create a webhook event instance from a raw payload array.
     *
     * @param array<string, mixed> $data The raw webhook payload.
     *
     * @return WebhookEventInterface
     *
     * @throws \Dropshipping\Exceptions\WebhookException If the event type is missing or unknown.
     */
    public static function fromArray(array $data): WebhookEventInterface
    {
        if (!isset($data['eventType'])) {
            throw new WebhookException('Missing eventType in webhook payload');
        }

        $eventType = WebhookEventType::tryFrom($data['eventType']);

        if ($eventType === null) {
            throw new WebhookException(sprintf('Unknown webhook event type: %s', $data['eventType']));
        }

        return match ($eventType) {
            WebhookEventType::Ping => PingEvent::fromArray($data),
            WebhookEventType::DeliveryShipment => DeliveryShipmentEvent::fromArray($data),
            WebhookEventType::DeliveryReturn => DeliveryReturnEvent::fromArray($data),
            WebhookEventType::DeliveryCancellation => DeliveryCancellationEvent::fromArray($data),
            WebhookEventType::LicensePlateReservationApproval => LicensePlateReservationApprovalEvent::fromArray($data),
            WebhookEventType::LicensePlateReservationRejection => LicensePlateReservationRejectionEvent::fromArray($data),
            WebhookEventType::LicensePlateReservationTimeout => LicensePlateReservationTimeoutEvent::fromArray($data),
            WebhookEventType::VehicleDeregistrationXkfzEvent => VehicleDeregistrationXkfzEvent::fromArray($data),
        };
    }
}
