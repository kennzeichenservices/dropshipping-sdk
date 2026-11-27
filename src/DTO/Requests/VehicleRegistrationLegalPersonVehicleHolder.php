<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;

/**
 * Request DTO representing a legal person (juristische Person) as the vehicle
 * holder. Requires API 2.7.0, which introduced the variant.
 *
 * The holder itself only carries `type: LEGAL_PERSON`. Everything else is in
 * {@see $personData}, which is either sent with the registration or taken from
 * a durable power of attorney, see
 * {@see VehicleRegistrationLegalPersonPersonDataInterface}.
 *
 * ## Not supported
 *
 * - A Halteränderung (`HA`).
 * - Reusing an earlier identification with `recycledVehicleRegistrationOrderId`.
 *
 * Both are rejected by {@see VehicleRegistrationRequest}.
 *
 * ## Up to SDK 2.7.3
 *
 * The class took name, industry code, company number, customs ID and address
 * directly and sent them flat on the holder, with the IBAN in the
 * customization. The 2.7.0 spec moved all of it into `personData`, so those now
 * go to {@see VehicleRegistrationLegalPersonProvidedPersonData}.
 */
final readonly class VehicleRegistrationLegalPersonVehicleHolder implements VehicleRegistrationVehicleHolderInterface
{
    /**
     * @param VehicleRegistrationLegalPersonPersonDataInterface $personData The data of the legal person, or the durable power of attorney it is taken from.
     */
    public function __construct(
        public VehicleRegistrationLegalPersonPersonDataInterface $personData,
    ) {
    }

    /**
     * Narrowed to a non-nullable return: only the pre-2.7.0 holder sends no
     * discriminator.
     */
    public function holderType(): VehicleRegistrationVehicleHolderType
    {
        return VehicleRegistrationVehicleHolderType::LegalPerson;
    }

    public function address(): ?Address
    {
        return $this->personData->address();
    }

    public function customsMajorCustomerId(): ?string
    {
        return $this->personData->customsMajorCustomerId();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => VehicleRegistrationVehicleHolderType::LegalPerson->value,
            'personData' => $this->personData->toArray(),
        ];
    }
}
