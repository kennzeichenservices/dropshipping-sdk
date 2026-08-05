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
        $rawEventType = $data['eventType'] ?? null;

        if ($rawEventType === null) {
            throw new WebhookException('Missing eventType in webhook payload');
        }

        // tryFrom() only accepts the enum's backing type, so a payload sending a number
        // or an object here would raise a TypeError instead of a WebhookException.
        if (!is_string($rawEventType)) {
            throw new WebhookException(sprintf(
                'Invalid eventType in webhook payload: expected string, got %s',
                get_debug_type($rawEventType),
            ));
        }

        $eventType = WebhookEventType::tryFrom($rawEventType);

        if ($eventType === null || $eventType === WebhookEventType::Unknown) {
            if ($tolerateUnknown) {
                return UnknownWebhookEvent::fromArray($data);
            }

            throw new WebhookException(sprintf('Unknown webhook event type: %s', $rawEventType));
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
            WebhookEventType::VehicleRegistrationXkfzEvent => VehicleRegistrationXkfzEvent::fromArray($data),
            WebhookEventType::VehicleRegistrationIdentityVerificationInitialized => VehicleRegistrationIdentityVerificationInitializedEvent::fromArray($data),
            WebhookEventType::VehicleRegistrationIdentityVerificationSucceeded => VehicleRegistrationIdentityVerificationSucceededEvent::fromArray($data),
            WebhookEventType::VehicleRegistrationIdentityVerificationFailed => VehicleRegistrationIdentityVerificationFailedEvent::fromArray($data),
            WebhookEventType::VehicleRegistrationDocumentSignatureInitialized => VehicleRegistrationDocumentSignatureInitializedEvent::fromArray($data),
            WebhookEventType::VehicleRegistrationDocumentSignatureSucceeded => VehicleRegistrationDocumentSignatureSucceededEvent::fromArray($data),
            WebhookEventType::VehicleRegistrationDocumentSignatureFailed => VehicleRegistrationDocumentSignatureFailedEvent::fromArray($data),
        };
    }
}
