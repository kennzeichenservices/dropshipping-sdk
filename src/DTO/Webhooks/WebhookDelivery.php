<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

/**
 * Represents a delivery reference within a webhook event payload.
 *
 * Contains the internal delivery ID and an optional shipment tracking code.
 */
final readonly class WebhookDelivery
{
    /**
     * @param int         $id           The internal delivery ID.
     * @param string|null $trackingCode The shipment tracking code, if available.
     */
    public function __construct(
        public int $id,
        public ?string $trackingCode = null,
    ) {
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
            id: $data['id'],
            trackingCode: $data['trackingCode'] ?? null,
        );
    }
}
