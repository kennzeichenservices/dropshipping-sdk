<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Enums\Gender;
use Dropshipping\Support\Hydrator;

/**
 * The signer of a durable power of attorney as the API returns it.
 *
 * The read-side counterpart of
 * {@see \Dropshipping\DTO\Requests\VehicleRegistrationDurablePowerOfAttorneySigner}.
 *
 * {@see $gender} is nullable although the spec requires it: it was added to API
 * 2.7.0 after the fact, so a record created before may come without it, and one
 * such record must not take the whole listing down. A value this SDK version
 * does not know reads null as well.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneySignerDetails
{
    private const CONTEXT = 'VehicleRegistrationDurablePowerOfAttorneySignerDetails';

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?Gender $gender = null,
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
            gender: Hydrator::enumOrNull(Gender::class, $data, 'gender'),
        );
    }
}
