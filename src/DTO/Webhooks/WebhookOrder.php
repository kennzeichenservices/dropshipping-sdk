<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

final readonly class WebhookOrder
{
    public function __construct(
        public int $id,
        public ?string $externalId = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            externalId: $data['externalId'] ?? null,
        );
    }
}
