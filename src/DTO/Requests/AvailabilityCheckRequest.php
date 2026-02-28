<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Support\Validator;

/**
 * Request DTO for checking license plate availability at a registration office.
 *
 * Contains the license plate components (city, middle, end), the plate type,
 * the vehicle type, and optional season month boundaries.
 */
final readonly class AvailabilityCheckRequest
{
    /**
     * @param int                $registrationOfficeServiceId ID of the registration office service.
     * @param string             $city                        City component of the license plate.
     * @param string             $middle                      Middle component of the license plate.
     * @param string             $end                         End component of the license plate.
     * @param LicensePlateType   $licensePlateType            Type of license plate.
     * @param VehicleType        $vehicleType                 Type of vehicle.
     * @param int|null           $seasonStartMonth            Optional start month for seasonal plates.
     * @param int|null           $seasonEndMonth              Optional end month for seasonal plates.
     */
    public function __construct(
        public int $registrationOfficeServiceId,
        public string $city,
        public string $middle,
        public string $end,
        public LicensePlateType $licensePlateType,
        public VehicleType $vehicleType,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        Validator::requireStringLength($city, 'city', 1, 3);
        Validator::requireStringLength($middle, 'middle', 1, 2);
        Validator::requireStringLength($end, 'end', 1, 4);
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
    }

    /**
     * Convert the request to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'registrationOfficeServiceId' => $this->registrationOfficeServiceId,
            'licensePlateNumberPatternComponents' => [
                'city' => $this->city,
                'middle' => $this->middle,
                'end' => $this->end,
            ],
            'licensePlateType' => $this->licensePlateType->value,
            'vehicleType' => $this->vehicleType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
