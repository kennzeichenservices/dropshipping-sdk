<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Represents a message attached to a vehicle registration XKFZ webhook event.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationXkfzEventMessage
{
    private const CONTEXT = 'VehicleRegistrationXkfzEventMessage';

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
            type: Hydrator::requireString($data, 'type', self::CONTEXT),
            kind: Hydrator::optionalString($data, 'kind', self::CONTEXT),
            code: Hydrator::optionalString($data, 'code', self::CONTEXT),
            text: Hydrator::optionalString($data, 'text', self::CONTEXT),
            additional: Hydrator::optionalString($data, 'additional', self::CONTEXT),
        );
    }
}
