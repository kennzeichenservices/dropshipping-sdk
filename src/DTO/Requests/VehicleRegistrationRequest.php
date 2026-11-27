<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Request DTO for creating a vehicle registration.
 *
 * Contains the customer email, customization details, vehicle holder
 * information, and optional external order ID and GKS configuration reference.
 *
 * ## Where the IBAN and the identity document go
 *
 * API 2.7.0 moved both onto the vehicle holder. Up to 2.6.0 the IBAN and BIC sit
 * in the customization and the identity document on the request itself. So
 * which fields are allowed follows from the holder class:
 *
 * - {@see VehicleRegistrationVehicleHolder} (up to 2.6.0): `customization.iban`
 *   is required for every service type code but `HA`, `bic` and
 *   {@see $identityVerificationDocumentType} sit where they always did.
 * - {@see VehicleRegistrationNaturalPersonVehicleHolder} and
 *   {@see VehicleRegistrationLegalPersonVehicleHolder} (2.7.0):
 *   `customization.iban`, `customization.bic` and
 *   {@see $identityVerificationDocumentType} must be null. The natural person
 *   carries all three itself, and its IBAN is required for every code but `HA`.
 *   A legal person carries IBAN and BIC in its person data, which checks them
 *   itself, or takes them from a durable power of attorney.
 *
 * ## Further rules spanning the holder and the customization
 *
 * - A legal person cannot order a Halteränderung (`HA`). See
 *   {@see \Dropshipping\Enums\VehicleRegistrationServiceTypeCode::supportsLegalPersonVehicleHolder()}.
 *
 * ## Reusing an earlier identification
 *
 * {@see $recycledVehicleRegistrationOrderId} points at an earlier registration
 * order whose identity verification and signed files this one takes over, so the
 * customer does not identify themselves and sign a second time. The field has
 * been in the spec since 2.6.0 but was only described in 2.7.0. It is not
 * supported for a legal person, which is checked here. The referenced order has
 * to have the files this registration needs: the power of attorney, the SEPA
 * direct debit mandate (except for `HA`), and the GDPR consent declaration of a
 * natural person (except for `HA`).
 *
 * That is not checkable here, because it is about the *other* order. The API
 * rejects a mismatch, so a rejected registration carrying this field is worth
 * checking against the list first.
 */
final readonly class VehicleRegistrationRequest
{
    /**
     * @param string                                    $email                              Customer email address.
     * @param VehicleRegistrationCustomization          $customization                      Vehicle registration customization details.
     * @param VehicleRegistrationVehicleHolderInterface $vehicleHolder                      Vehicle holder information. Which implementation to pass depends on the API version, see {@see VehicleRegistrationVehicleHolderInterface}.
     * @param string|null                               $externalOrderId                    Optional external order identifier.
     * @param string|null                               $gksConfigurationId                 Optional UUID of the GKS configuration to use.
     * @param string|null                               $contractPartnerKopaKey             Optional (KBA) Vertragspartner-Kopa (Schlüssel) to include in the registration request.
     * @param IdentityVerificationDocumentType|null     $identityVerificationDocumentType   Document the customer identifies themselves with, for API 2.6.0 only: required there, unknown to 2.4.0 and 2.5.0, and moved onto the natural person holder in 2.7.0. So it must be null with a 2.7.0 holder, and is left out of the payload when null. Correct it after the fact with {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::updateIdentityVerificationDocumentType()}.
     * @param int|null                                  $recycledVehicleRegistrationOrderId Order ID of an earlier vehicle registration whose identity verification and signed files are reused, so the customer goes through neither a second time. Requires API 2.6.0 and is not supported for a legal person. Serialized as `recycledVehicleRegistration.orderId`, and left out of the payload when null. See the class docblock for what the referenced order has to satisfy.
     *
     * @throws DropshippingException When a value violates the API constraints, or
     *                               when the holder and the customization contradict
     *                               each other.
     */
    public function __construct(
        public string $email,
        public VehicleRegistrationCustomization $customization,
        public VehicleRegistrationVehicleHolderInterface $vehicleHolder,
        public ?string $externalOrderId = null,
        public ?string $gksConfigurationId = null,
        public ?string $contractPartnerKopaKey = null,
        public ?IdentityVerificationDocumentType $identityVerificationDocumentType = null,
        public ?int $recycledVehicleRegistrationOrderId = null,
    ) {
        Validator::requireStringLength($email, 'email', 3, 255);
        Validator::requireEmail($email, 'email');
        Validator::requireNullableStringLength($externalOrderId, 'externalOrderId', 1, 100);
        Validator::requireNullableStringLength($gksConfigurationId, 'gksConfigurationId', 1, 255);
        Validator::requireNullableStringLength($contractPartnerKopaKey, 'contractPartnerKopaKey', 1, 20);
        // An int64 order ID the API assigned. Zero and negatives cannot name one, and
        // sending them costs a round trip to learn that.
        Validator::requireNullableIntRange($recycledVehicleRegistrationOrderId, 'recycledVehicleRegistrationOrderId', 1, PHP_INT_MAX);

        $serviceTypeCode = $customization->vehicleRegistrationServiceTypeCode;
        $forCode = sprintf('for vehicleRegistrationServiceTypeCode %s', $serviceTypeCode->value);
        $holderType = $vehicleHolder->holderType();

        if ($holderType === null) {
            if ($serviceTypeCode->requiresInsuranceAndTaxDetails()) {
                Validator::requireNotNull($customization->iban, 'customization.iban', $forCode);
            }

            return;
        }

        $forHolderType = sprintf('for a vehicleHolder of type %s, which carries it itself', $holderType->value);

        Validator::requireNull($customization->iban, 'customization.iban', $forHolderType);
        Validator::requireNull($customization->bic, 'customization.bic', $forHolderType);
        Validator::requireNull($identityVerificationDocumentType, 'identityVerificationDocumentType', $forHolderType);

        if ($vehicleHolder instanceof VehicleRegistrationNaturalPersonVehicleHolder && $serviceTypeCode->requiresInsuranceAndTaxDetails()) {
            Validator::requireNotNull($vehicleHolder->iban, 'vehicleHolder.iban', $forCode);
        }

        if ($holderType === VehicleRegistrationVehicleHolderType::LegalPerson) {
            if (!$serviceTypeCode->supportsLegalPersonVehicleHolder()) {
                throw new DropshippingException(sprintf(
                    'A vehicleHolder of type %s is not supported for vehicleRegistrationServiceTypeCode %s',
                    $holderType->value,
                    $serviceTypeCode->value,
                ));
            }

            Validator::requireNull(
                $recycledVehicleRegistrationOrderId,
                'recycledVehicleRegistrationOrderId',
                sprintf('for a vehicleHolder of type %s', $holderType->value),
            );
        }
    }

    /**
     * Convert the registration request to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'externalOrderId' => $this->externalOrderId,
            'email' => $this->email,
            'customization' => $this->customization->toArray(),
            'vehicleHolder' => $this->vehicleHolder->toArray(),
            'gksConfigurationId' => $this->gksConfigurationId,
            'contractPartnerKopaKey' => $this->contractPartnerKopaKey,
            'identityVerificationDocumentType' => $this->identityVerificationDocumentType?->value,
            'recycledVehicleRegistration' => $this->recycledVehicleRegistrationOrderId === null
                ? null
                : ['orderId' => $this->recycledVehicleRegistrationOrderId],
        ], static fn (mixed $value): bool => $value !== null);
    }
}
