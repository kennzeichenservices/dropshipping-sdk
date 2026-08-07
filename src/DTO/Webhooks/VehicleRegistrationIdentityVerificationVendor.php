<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Identifies the vendor handling identity verification and document signing
 * for a vehicle registration.
 */
final readonly class VehicleRegistrationIdentityVerificationVendor
{
    private const CONTEXT = 'VehicleRegistrationIdentityVerificationVendor';

    /**
     * @param int $id The vendor ID.
     */
    public function __construct(
        public int $id,
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
        );
    }
}
