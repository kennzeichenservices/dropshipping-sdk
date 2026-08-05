<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when a license plate reservation is rejected.
 *
 * Contains the order reference, customization details, and a list
 * of proposed alternative license plate number components.
 */
final readonly class LicensePlateReservationRejectionEvent implements WebhookEventInterface
{
    private const CONTEXT = 'LicensePlateReservationRejectionEvent';

    /**
     * @param string                                 $eventTime                                      The ISO 8601 timestamp when the rejection occurred.
     * @param WebhookOrder                           $order                                          The associated order reference.
     * @param LicensePlateReservationCustomization   $customization                                  The license plate customization details.
     * @param list<EuroLicensePlateNumberComponents> $proposedAlternativeLicensePlateNumberComponents Proposed alternative license plate components.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public LicensePlateReservationCustomization $customization,
        public array $proposedAlternativeLicensePlateNumberComponents = [],
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::LicensePlateReservationRejection;
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
            proposedAlternativeLicensePlateNumberComponents: array_map(
                static fn (array $item): EuroLicensePlateNumberComponents => EuroLicensePlateNumberComponents::fromArray($item),
                Hydrator::optionalArrayList($data, 'proposedAlternativeLicensePlateNumberComponents', self::CONTEXT),
            ),
        );
    }
}
