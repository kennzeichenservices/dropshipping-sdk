<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

/**
 * Represents a message attached to a vehicle deregistration XKFZ webhook event.
 */
final readonly class VehicleDeregistrationXkfzEventMessage
{
    /**
     * @param string      $type       The message type.
     * @param string|null $kind       Optional message kind.
     * @param string|null $code       Optional message code.
     * @param string|null $text       Optional message text.
     * @param string|null $additional Optional additional information.
     */
    public function __construct(
        public string $type,
        public ?string $kind,
        public ?string $code,
        public ?string $text,
        public ?string $additional,
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
            type: $data['type'],
            kind: $data['kind'] ?? null,
            code: $data['code'] ?? null,
            text: $data['text'] ?? null,
            additional: $data['additional'] ?? null,
        );
    }
}
