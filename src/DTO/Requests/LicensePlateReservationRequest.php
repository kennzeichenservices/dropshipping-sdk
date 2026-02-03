<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

/**
 * Request DTO for creating a license plate reservation.
 *
 * Contains the customer email, customization details, vehicle holder
 * information, and an optional external order ID.
 */
final readonly class LicensePlateReservationRequest
{
    /**
     * @param string                                $email          Customer email address.
     * @param LicensePlateReservationCustomization  $customization  License plate customization details.
     * @param LicensePlateReservationVehicleHolder  $vehicleHolder  Vehicle holder information.
     * @param string|null                           $externalOrderId Optional external order identifier.
     */
    public function __construct(
        public string $email,
        public LicensePlateReservationCustomization $customization,
        public LicensePlateReservationVehicleHolder $vehicleHolder,
        public ?string $externalOrderId = null,
    ) {
    }

    /**
     * Convert the reservation request to an associative array, excluding null values.
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
        ], static fn (mixed $value): bool => $value !== null);
    }
}
