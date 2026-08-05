<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when a license plate reservation times out.
 *
 * Contains the order reference and the customization details
 * of the expired reservation.
 */
final readonly class LicensePlateReservationTimeoutEvent implements WebhookEventInterface
{
    private const CONTEXT = 'LicensePlateReservationTimeoutEvent';

    /**
     * @param string                               $eventTime     The ISO 8601 timestamp when the timeout occurred.
     * @param WebhookOrder                         $order         The associated order reference.
     * @param LicensePlateReservationCustomization $customization The license plate customization details.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public LicensePlateReservationCustomization $customization,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::LicensePlateReservationTimeout;
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
            customization: LicensePlateReservationCustomization::fromArray(
                Hydrator::requireArray($data, 'customization', self::CONTEXT),
            ),
        );
    }
}
