<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when a license plate reservation is approved.
 *
 * Contains the order reference, reservation PIN, customization details,
 * the reservation price and, since webhooks 3.3.0, how long the reservation
 * lasts.
 */
final readonly class LicensePlateReservationApprovalEvent implements WebhookEventInterface
{
    private const CONTEXT = 'LicensePlateReservationApprovalEvent';

    /**
     * @param string                               $eventTime        The ISO 8601 timestamp when the approval occurred.
     * @param WebhookOrder                         $order            The associated order reference.
     * @param string|null                          $reservationPin   The reservation PIN, if provided.
     * @param LicensePlateReservationCustomization $customization    The license plate customization details.
     * @param string                               $reservationPrice The price for the reservation.
     * @param int|null                             $reservationDurationDays Number of days the license plate is reserved for. Null when the API does not send it, which events before webhooks 3.3.0 never do.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public ?string $reservationPin,
        public LicensePlateReservationCustomization $customization,
        public string $reservationPrice,
        public ?int $reservationDurationDays = null,
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
            eventTime: Hydrator::requireString($data, 'eventTime', self::CONTEXT),
            order: WebhookOrder::fromArray(Hydrator::requireArray($data, 'order', self::CONTEXT)),
            reservationPin: Hydrator::optionalString($data, 'reservationPin', self::CONTEXT),
            customization: LicensePlateReservationCustomization::fromArray(
                Hydrator::requireArray($data, 'customization', self::CONTEXT),
            ),
            reservationPrice: Hydrator::requireString($data, 'reservationPrice', self::CONTEXT),
            reservationDurationDays: Hydrator::optionalInt($data, 'reservationDurationDays', self::CONTEXT),
        );
    }
}
