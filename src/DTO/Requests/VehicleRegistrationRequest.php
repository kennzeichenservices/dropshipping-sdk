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
 * ## Rules spanning the holder and the customization
 *
 * This is the only place where both are known, so the rules connecting them are
 * checked here rather than in either DTO:
 *
 * - A legal person cannot order a Halteränderung (`HA`). See
 *   {@see \Dropshipping\Enums\VehicleRegistrationServiceTypeCode::supportsLegalPersonVehicleHolder()}.
 * - A legal person carrying a `customsMajorCustomerId` settles the motor vehicle
 *   tax through the customs administration, which replaces the SEPA direct debit
 *   mandate: `customization.iban` and `customization.bic` must both be empty
 *   then.
 * - Without such an ID, `customization.iban` is required for every service type
 *   code but `HA`. See
 *   {@see \Dropshipping\Enums\VehicleRegistrationServiceTypeCode::requiresInsuranceAndTaxDetails()}.
 *
 * Both of the last two were enforced by
 * {@see VehicleRegistrationCustomization} up to SDK 2.6.0, where the IBAN was
 * simply required for every code but `HA`.
 *
 * ## Reusing an earlier identification
 *
 * {@see $recycledVehicleRegistrationOrderId} points at an earlier registration
 * order whose identity verification and signed files this one takes over, so the
 * customer does not identify themselves and sign a second time. The field has
 * been in the spec since 2.6.0 but was only described in 2.7.0, which lists what
 * the referenced order has to satisfy:
 *
 * - the same `vehicleHolder.type`, and for a legal person the same
 *   `vehicleHolder.name`, compared case insensitively and ignoring surrounding
 *   whitespace;
 * - the files this registration needs: the power of attorney, the SEPA direct
 *   debit mandate (except for `HA` and for a holder with a
 *   `customsMajorCustomerId`), and the GDPR consent declaration of a natural
 *   person (except for `HA`).
 *
 * None of that is checkable here, because it is all about the *other* order. The
 * API rejects a mismatch, so a rejected registration carrying this field is
 * worth checking against the list above first.
 */
final readonly class VehicleRegistrationRequest
{
    /**
     * @param string                                 $email                            Customer email address.
     * @param VehicleRegistrationCustomization       $customization                    Vehicle registration customization details.
     * @param VehicleRegistrationVehicleHolderInterface $vehicleHolder                 Vehicle holder information. Which implementation to pass depends on the API version, see {@see VehicleRegistrationVehicleHolderInterface}.
     * @param string|null                            $externalOrderId                  Optional external order identifier.
     * @param string|null                            $gksConfigurationId               Optional UUID of the GKS configuration to use.
     * @param string|null                            $contractPartnerKopaKey           Optional (KBA) Vertragspartner-Kopa (Schlüssel) to include in the registration request.
     * @param IdentityVerificationDocumentType|null  $identityVerificationDocumentType Document the customer identifies themselves with. Required by API 2.6.0 and rejected with 400 when omitted there; not part of 2.4.0 or 2.5.0, so it stays optional here and is left out of the payload when null. Correct it after the fact with {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::updateIdentityVerificationDocumentType()}.
     * @param int|null                               $recycledVehicleRegistrationOrderId Order ID of an earlier vehicle registration whose identity verification and signed files are reused, so the customer goes through neither a second time. Requires API 2.6.0. Serialized as `recycledVehicleRegistration.orderId`, and left out of the payload when null. See the class docblock for what the referenced order has to satisfy.
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

        if (
            $vehicleHolder->holderType() === VehicleRegistrationVehicleHolderType::LegalPerson
            && !$serviceTypeCode->supportsLegalPersonVehicleHolder()
        ) {
            throw new DropshippingException(sprintf(
                'A vehicleHolder of type %s is not supported for vehicleRegistrationServiceTypeCode %s',
                VehicleRegistrationVehicleHolderType::LegalPerson->value,
                $serviceTypeCode->value,
            ));
        }

        if ($vehicleHolder->customsMajorCustomerId() !== null) {
            $whenCustoms = 'when vehicleHolder.customsMajorCustomerId is set';

            Validator::requireNull($customization->iban, 'customization.iban', $whenCustoms);
            Validator::requireNull($customization->bic, 'customization.bic', $whenCustoms);
        } elseif ($serviceTypeCode->requiresInsuranceAndTaxDetails()) {
            Validator::requireNotNull(
                $customization->iban,
                'customization.iban',
                sprintf('for vehicleRegistrationServiceTypeCode %s', $serviceTypeCode->value),
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
