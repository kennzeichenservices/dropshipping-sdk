<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\VehicleRegistrationCustomization;
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
     * @param string                            $email                  Customer email address.
     * @param VehicleRegistrationCustomization  $customization          Vehicle registration customization details.
     * @param VehicleRegistrationVehicleHolder  $vehicleHolder          Vehicle holder information.
     * @param string|null                       $externalOrderId        Optional external order identifier.
     * @param string|null                       $gksConfigurationId     Optional UUID of the GKS configuration to use.
     * @param string|null                       $contractPartnerKopaKey Optional (KBA) Vertragspartner-Kopa (Schlüssel) to include in the registration request.
     */
    public function __construct(
        public string $email,
        public VehicleRegistrationCustomization $customization,
        public VehicleRegistrationVehicleHolder $vehicleHolder,
        public ?string $externalOrderId = null,
        public ?string $gksConfigurationId = null,
        public ?string $contractPartnerKopaKey = null,
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
        ], static fn (mixed $value): bool => $value !== null);
    }
}
