<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

/**
 * Represents an order reference within a webhook event payload.
 *
 * Contains the internal order ID and an optional external ID
 * provided by the integrating system.
 */
final readonly class WebhookOrder
{
    /**
     * @param int         $id         The internal order ID.
     * @param string|null $externalId The external order ID, if available.
     */
    public function __construct(
        public int $id,
        public ?string $externalId = null,
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
            externalId: $data['externalId'] ?? null,
        );
    }
}
