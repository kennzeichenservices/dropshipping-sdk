<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Support\Validator;

/**
 * Data transfer object describing the license plate the vehicle carried before
 * the registration, used when the previous number is retained or handed in.
 */
final readonly class VehicleRegistrationPreviousLicensePlate
{
    /**
     * @param EuroLicensePlateNumberComponents      $licensePlateNumberComponents  Components of the previous license plate number.
     * @param VehicleRegistrationLicensePlateType   $licensePlateType              Type of the previous license plate.
     * @param int|null                              $seasonStartMonth              Start month for seasonal plates (1–12).
     * @param int|null                              $seasonEndMonth                End month for seasonal plates (1–12).
     * @param string|null                           $frontLicensePlateSecurityCode Security code from the front license plate (exactly 3 characters).
     * @param string|null                           $rearLicensePlateSecurityCode  Security code from the rear license plate (exactly 3 characters).
     */
    public function __construct(
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public VehicleRegistrationLicensePlateType $licensePlateType,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
        public ?string $frontLicensePlateSecurityCode = null,
        public ?string $rearLicensePlateSecurityCode = null,
    ) {
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
        Validator::requireNullableStringLength($frontLicensePlateSecurityCode, 'frontLicensePlateSecurityCode', 3, 3);
        Validator::requireNullableStringLength($rearLicensePlateSecurityCode, 'rearLicensePlateSecurityCode', 3, 3);
    }

    /**
     * Convert the previous license plate to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'licensePlateType' => $this->licensePlateType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
            'frontLicensePlateSecurityCode' => $this->frontLicensePlateSecurityCode,
            'rearLicensePlateSecurityCode' => $this->rearLicensePlateSecurityCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
