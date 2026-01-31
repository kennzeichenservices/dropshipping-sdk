<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\Enums\WebhookEventType;

final readonly class LicensePlateReservationRejectionEvent implements WebhookEventInterface
{
    /**
     * @param list<EuroLicensePlateNumberComponents> $proposedAlternativeLicensePlateNumberComponents
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public LicensePlateReservationCustomization $customization,
        public array $proposedAlternativeLicensePlateNumberComponents = [],
    ) {
    }

    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::LicensePlateReservationRejection;
    }

    public function getEventTime(): string
    {
        return $this->eventTime;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            eventTime: $data['eventTime'],
            order: WebhookOrder::fromArray($data['order']),
            customization: LicensePlateReservationCustomization::fromArray($data['customization']),
            proposedAlternativeLicensePlateNumberComponents: array_map(
                static fn (array $item): EuroLicensePlateNumberComponents => EuroLicensePlateNumberComponents::fromArray($item),
                $data['proposedAlternativeLicensePlateNumberComponents'] ?? [],
            ),
        );
    }
}
