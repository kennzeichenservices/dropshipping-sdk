<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Represents a delivery reference within a webhook event payload.
 *
 * Contains the internal delivery ID and an optional shipment tracking code.
 */
final readonly class WebhookDelivery
{
    private const CONTEXT = 'WebhookDelivery';

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
            id: Hydrator::requireInt($data, 'id', self::CONTEXT),
            trackingCode: Hydrator::optionalString($data, 'trackingCode', self::CONTEXT),
        );
    }
}
