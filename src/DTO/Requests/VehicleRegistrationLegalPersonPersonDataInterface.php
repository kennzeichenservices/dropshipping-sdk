<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationLegalPersonPersonDataType;

/**
 * The data of a legal person vehicle holder, `vehicleHolder.personData` in API
 * 2.7.0.
 *
 * The API discriminates the variant by the `type` property, so every
 * implementation serializes its own {@see type()}:
 *
 * - {@see VehicleRegistrationLegalPersonProvidedPersonData}: name, company
 *   number, industry code, address and the vehicle tax details, sent with the
 *   registration.
 * - {@see VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData}:
 *   the ID of a durable power of attorney the API takes all of that from.
 */
interface VehicleRegistrationLegalPersonPersonDataInterface
{
    /**
     * The discriminator value identifying this variant.
     */
    public function type(): VehicleRegistrationLegalPersonPersonDataType;

    /**
     * The address of the legal person, or null where the API takes it from a
     * durable power of attorney.
     */
    public function address(): ?Address;

    /**
     * The major customer ID of the customs administration, or null where the
     * legal person has none or the API takes it from a durable power of
     * attorney.
     */
    public function customsMajorCustomerId(): ?string;

    /**
     * Convert the person data to an associative array, discriminator included.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
