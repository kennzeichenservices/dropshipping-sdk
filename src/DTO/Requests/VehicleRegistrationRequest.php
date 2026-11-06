<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Support\Validator;

/**
 * Request DTO for creating a vehicle registration.
 *
 * Contains the customer email, customization details, vehicle holder
 * information, and optional external order ID and GKS configuration reference.
 */
final readonly class VehicleRegistrationRequest
{
    /**
     * @param string                                 $email                            Customer email address.
     * @param VehicleRegistrationCustomization       $customization                    Vehicle registration customization details.
     * @param VehicleRegistrationVehicleHolder       $vehicleHolder                    Vehicle holder information.
     * @param string|null                            $externalOrderId                  Optional external order identifier.
     * @param string|null                            $gksConfigurationId               Optional UUID of the GKS configuration to use.
     * @param string|null                            $contractPartnerKopaKey           Optional (KBA) Vertragspartner-Kopa (Schlüssel) to include in the registration request.
     * @param IdentityVerificationDocumentType|null  $identityVerificationDocumentType Document the customer identifies themselves with. Required by API 2.6.0 and rejected with 400 when omitted there; not part of 2.4.0 or 2.5.0, so it stays optional here and is left out of the payload when null. Correct it after the fact with {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::updateIdentityVerificationDocumentType()}.
     */
    public function __construct(
        public string $email,
        public VehicleRegistrationCustomization $customization,
        public VehicleRegistrationVehicleHolder $vehicleHolder,
        public ?string $externalOrderId = null,
        public ?string $gksConfigurationId = null,
        public ?string $contractPartnerKopaKey = null,
        public ?IdentityVerificationDocumentType $identityVerificationDocumentType = null,
    ) {
        Validator::requireStringLength($email, 'email', 3, 255);
        Validator::requireEmail($email, 'email');
        Validator::requireNullableStringLength($externalOrderId, 'externalOrderId', 1, 100);
        Validator::requireNullableStringLength($gksConfigurationId, 'gksConfigurationId', 1, 255);
        Validator::requireNullableStringLength($contractPartnerKopaKey, 'contractPartnerKopaKey', 1, 20);
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
        ], static fn (mixed $value): bool => $value !== null);
    }
}
