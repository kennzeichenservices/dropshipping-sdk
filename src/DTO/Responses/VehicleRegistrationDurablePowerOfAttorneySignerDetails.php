<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * The signer of a durable power of attorney as the API returns it.
 *
 * The read-side counterpart of
 * {@see \Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneySigner}.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneySignerDetails
{
    private const CONTEXT = 'VehicleRegistrationDurablePowerOfAttorneySignerDetails';

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: Hydrator::requireString($data, 'firstName', self::CONTEXT),
            lastName: Hydrator::requireString($data, 'lastName', self::CONTEXT),
            email: Hydrator::requireString($data, 'email', self::CONTEXT),
        );
    }
}
