<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\Enums\WebhookEventType;

/**
 * Webhook event fired when a license plate reservation is approved.
 *
 * Contains the order reference, reservation PIN, customization details,
 * and the reservation price.
 */
final readonly class LicensePlateReservationApprovalEvent implements WebhookEventInterface
{
    /**
     * @param string                               $eventTime        The ISO 8601 timestamp when the approval occurred.
     * @param WebhookOrder                         $order            The associated order reference.
     * @param string|null                          $reservationPin   The reservation PIN, if provided.
     * @param LicensePlateReservationCustomization $customization    The license plate customization details.
     * @param string                               $reservationPrice The price for the reservation.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public ?string $reservationPin,
        public LicensePlateReservationCustomization $customization,
        public string $reservationPrice,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::LicensePlateReservationApproval;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventTime(): string
    {
        return $this->eventTime;
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            eventTime: $data['eventTime'],
            order: WebhookOrder::fromArray($data['order']),
            reservationPin: $data['reservationPin'] ?? null,
            customization: LicensePlateReservationCustomization::fromArray($data['customization']),
            reservationPrice: $data['reservationPrice'],
        );
    }
}
