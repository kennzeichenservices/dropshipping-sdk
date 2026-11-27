<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\VehicleRegistrationLegalPersonPersonDataType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Person data of a legal person vehicle holder that the API takes from a
 * durable power of attorney (Dauervollmacht). Requires API 2.7.0.
 *
 * Name, company number, industry code, address and how the vehicle tax is
 * settled are all part of the power of attorney, so this class carries its ID
 * and nothing else.
 *
 * ## Which powers of attorney can be named
 *
 * The API accepts one that is COMPLETE, active and not expired. The SDK checks
 * that the ID is a UUID; the rest is only known to the API. Look the power of
 * attorney up first and check
 * {@see \Dropshipping\DTO\Responses\VehicleRegistrationDurablePowerOfAttorney::isUsable()}.
 */
final readonly class VehicleRegistrationLegalPersonDurablePowerOfAttorneyReferencePersonData implements VehicleRegistrationLegalPersonPersonDataInterface
{
    /**
     * @param string $durablePowerOfAttorneyId UUID of the durable power of attorney, as returned when it was created.
     *
     * @throws DropshippingException When the ID is not a UUID.
     */
    public function __construct(
        public string $durablePowerOfAttorneyId,
    ) {
        Validator::requireUuid($durablePowerOfAttorneyId, 'personData.durablePowerOfAttorneyId');
    }

    public function type(): VehicleRegistrationLegalPersonPersonDataType
    {
        return VehicleRegistrationLegalPersonPersonDataType::DurablePowerOfAttorneyReference;
    }

    /**
     * Always null: the address is part of the power of attorney.
     */
    public function address(): null
    {
        return null;
    }

    /**
     * Always null. The power of attorney may well carry a customs major customer
     * ID, but this side does not know it.
     */
    public function customsMajorCustomerId(): null
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'durablePowerOfAttorneyId' => $this->durablePowerOfAttorneyId,
        ];
    }
}
