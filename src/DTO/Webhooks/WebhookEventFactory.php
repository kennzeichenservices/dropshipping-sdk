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
     * @param array<string, mixed> $data            The raw webhook payload.
     * @param bool                 $tolerateUnknown When true, an unrecognised eventType yields an
     *                                              {@see UnknownWebhookEvent} instead of an exception.
     *                                              Useful while the API rolls out beta events this SDK
     *                                              version does not model yet.
     *
     * @return WebhookEventInterface
     *
     * @throws \Dropshipping\Exceptions\WebhookException If the event type is missing, or unknown
     *                                                  while $tolerateUnknown is false.
     */
    public static function fromArray(array $data, bool $tolerateUnknown = false): WebhookEventInterface
    {
        if (!isset($data['eventType'])) {
            throw new WebhookException('Missing eventType in webhook payload');
        }

        $eventType = WebhookEventType::tryFrom($data['eventType']);

        if ($eventType === null || $eventType === WebhookEventType::Unknown) {
            if ($tolerateUnknown) {
                return UnknownWebhookEvent::fromArray($data);
            }

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
