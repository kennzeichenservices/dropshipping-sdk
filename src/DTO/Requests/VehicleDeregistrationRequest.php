<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\VehicleDeregistrationCustomization;
use Dropshipping\Support\Validator;

/**
 * Request DTO for creating a vehicle deregistration.
 *
 * Contains the customer email, customization details, vehicle holder
 * information, and optional external order ID and GKS configuration reference.
 */
final readonly class VehicleDeregistrationRequest
{
    /**
     * @param string                              $email              Customer email address.
     * @param VehicleDeregistrationCustomization  $customization      Vehicle deregistration customization details.
     * @param VehicleDeregistrationVehicleHolder  $vehicleHolder      Vehicle holder information.
     * @param string|null                         $externalOrderId    Optional external order identifier.
     * @param string|null                         $gksConfigurationId Optional UUID of the GKS configuration to use.
     */
    public function __construct(
        public string $email,
        public VehicleDeregistrationCustomization $customization,
        public VehicleDeregistrationVehicleHolder $vehicleHolder,
        public ?string $externalOrderId = null,
        public ?string $gksConfigurationId = null,
    ) {
        Validator::requireStringLength($email, 'email', 3, 255);
        Validator::requireEmail($email, 'email');
        Validator::requireNullableStringLength($externalOrderId, 'externalOrderId', 1, 100);
        Validator::requireNullableStringLength($gksConfigurationId, 'gksConfigurationId', 1, 255);
    }

    /**
     * Convert the deregistration request to an associative array, excluding null values.
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
        ], static fn (mixed $value): bool => $value !== null);
    }
}
