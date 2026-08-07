<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Assignment strategy using a previously reserved license plate number.
 */
final readonly class VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation implements VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface
{
    /**
     * @param EuroLicensePlateNumberComponents    $licensePlateNumberComponents Components of the reserved license plate number.
     * @param VehicleRegistrationLicensePlateType $licensePlateType             Type of license plate to issue.
     * @param string                              $reservationPin               PIN of the reservation, 4–12 characters.
     * @param int|null                            $seasonStartMonth             Start month for seasonal plates (1–12).
     * @param int|null                            $seasonEndMonth               End month for seasonal plates (1–12).
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public VehicleRegistrationLicensePlateType $licensePlateType,
        public string $reservationPin,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        Validator::requireStringLength($reservationPin, 'reservationPin', 4, 12);
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
    }

    public function strategyType(): VehicleRegistrationLicensePlateNumberAssignmentStrategyType
    {
        return VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Reservation;
    }

    /**
     * Convert the strategy to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'strategyType' => $this->strategyType()->value,
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'licensePlateType' => $this->licensePlateType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
            'reservationPin' => $this->reservationPin,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
