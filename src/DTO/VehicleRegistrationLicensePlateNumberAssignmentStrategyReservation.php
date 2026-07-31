<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Assignment strategy using a previously reserved license plate number.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation implements VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface
{
    /**
     * @param VehicleRegistrationLicensePlate $licensePlate   The reserved license plate.
     * @param string                          $reservationPin PIN of the reservation, 4–12 characters.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public VehicleRegistrationLicensePlate $licensePlate,
        public string $reservationPin,
    ) {
        Validator::requireStringLength($reservationPin, 'reservationPin', 4, 12);
    }

    public function strategyType(): VehicleRegistrationLicensePlateNumberAssignmentStrategyType
    {
        return VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Reservation;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'strategyType' => $this->strategyType()->value,
            'licensePlate' => $this->licensePlate->toArray(),
            'reservationPin' => $this->reservationPin,
        ];
    }
}
