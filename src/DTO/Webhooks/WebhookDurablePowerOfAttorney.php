<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Reference to a durable power of attorney within a webhook event payload.
 *
 * Carries only the ID. Fetch the full record with
 * {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::getDurablePowerOfAttorney()}.
 */
final readonly class WebhookDurablePowerOfAttorney
{
    private const CONTEXT = 'WebhookDurablePowerOfAttorney';

    /**
     * @param string $id UUID of the durable power of attorney.
     */
    public function __construct(
        public string $id,
    ) {
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Hydrator::requireString($data, 'id', self::CONTEXT),
        );
    }
}
