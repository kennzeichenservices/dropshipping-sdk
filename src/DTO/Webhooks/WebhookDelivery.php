<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

final readonly class WebhookDelivery
{
    public function __construct(
        public int $id,
        public ?string $trackingCode = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            trackingCode: $data['trackingCode'] ?? null,
        );
    }
}
