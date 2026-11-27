<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Iban;
use Dropshipping\Support\Validator;

/**
 * Request DTO representing a natural person as the vehicle holder, in the shape
 * API 2.7.0 expects.
 *
 * Carries the fields of {@see VehicleRegistrationVehicleHolder} plus what 2.7.0
 * moved onto the holder: the `type` discriminator, the identity document, and
 * the IBAN and BIC for the vehicle tax. Which of the two classes to use follows
 * from the API version the integration runs against, nothing else:
 *
 * - 2.7.0 and up: this class.
 * - 2.4.0 to 2.6.0: {@see VehicleRegistrationVehicleHolder}, with the identity
 *   document on the request and the IBAN in the customization.
 *
 * {@see $iban} is required for every service type code but `HA`. The holder does
 * not know the code, so {@see VehicleRegistrationRequest} checks that.
 *
 * @see VehicleRegistrationLegalPersonVehicleHolder for a company as the holder.
 */
final readonly class VehicleRegistrationNaturalPersonVehicleHolder implements VehicleRegistrationVehicleHolderInterface
{
    /**
     * @param Address                          $address                          Address of the vehicle holder.
     * @param string                           $placeOfBirth                     Place of birth of the vehicle holder, 1–150 characters.
     * @param string                           $birthDate                        Birth date of the vehicle holder in ISO 8601 format (e.g. 1990-01-31).
     * @param IdentityVerificationDocumentType $identityVerificationDocumentType Document the holder identifies themselves with. Correct it after the fact with {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::updateIdentityVerificationDocumentType()}.
     * @param string|null                      $birthName                        Optional birth name of the vehicle holder, 1–100 characters.
     * @param string|null                      $iban                             IBAN for the recurring vehicle tax debit, 15–34 characters. Required for every service type code but HA.
     * @param string|null                      $bic                              BIC belonging to the IBAN, 8–11 characters. Required for an IBAN starting with MC, SM or CH, optional otherwise.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public Address $address,
        public string $placeOfBirth,
        public string $birthDate,
        public IdentityVerificationDocumentType $identityVerificationDocumentType,
        public ?string $birthName = null,
        public ?string $iban = null,
        public ?string $bic = null,
    ) {
        Validator::requireStringLength($placeOfBirth, 'placeOfBirth', 1, 150);
        Validator::requireNonEmpty($birthDate, 'birthDate');
        Validator::requireNullableStringLength($birthName, 'birthName', 1, 100);
        Validator::requireNullableStringLength($iban, 'vehicleHolder.iban', 15, 34);
        Validator::requireNullableStringLength($bic, 'vehicleHolder.bic', 8, 11);

        if ($iban !== null && Iban::requiresBic($iban)) {
            Validator::requireNotNull($bic, 'vehicleHolder.bic', sprintf('for an iban starting with %s', mb_substr($iban, 0, 2)));
        }
    }

    /**
     * Narrowed to a non-nullable return: only the pre-2.7.0 holder sends no
     * discriminator.
     */
    public function holderType(): VehicleRegistrationVehicleHolderType
    {
        return VehicleRegistrationVehicleHolderType::NaturalPerson;
    }

    public function address(): Address
    {
        return $this->address;
    }

    /**
     * Always null: only a legal person can have a customs major customer ID.
     */
    public function customsMajorCustomerId(): ?string
    {
        return null;
    }

    /**
     * Convert the vehicle holder data to an associative array, omitting the
     * optional fields when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => VehicleRegistrationVehicleHolderType::NaturalPerson->value,
            'identityVerificationDocumentType' => $this->identityVerificationDocumentType->value,
            'address' => $this->address->toArray(),
            'placeOfBirth' => $this->placeOfBirth,
            'birthDate' => $this->birthDate,
            'birthName' => $this->birthName,
            'iban' => $this->iban,
            'bic' => $this->bic,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
