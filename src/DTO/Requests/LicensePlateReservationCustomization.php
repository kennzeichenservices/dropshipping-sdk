<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Support\Hydrator;
use Dropshipping\Support\Validator;

/**
 * DTO for license plate reservation customization details.
 *
 * Contains the registration office, plate and vehicle type,
 * license plate number components, and optional season months.
 */
final readonly class LicensePlateReservationCustomization
{
    private const CONTEXT = 'LicensePlateReservationCustomization';

    /**
     * @param int                              $registrationOfficeServiceId ID of the registration office service.
     * @param LicensePlateType                 $licensePlateType            Type of license plate.
     * @param VehicleType                      $vehicleType                 Type of vehicle.
     * @param EuroLicensePlateNumberComponents $licensePlateNumberComponents License plate number components.
     * @param int|null                         $seasonStartMonth            Optional start month for seasonal plates.
     * @param int|null                         $seasonEndMonth              Optional end month for seasonal plates.
     */
    public function __construct(
        public int $registrationOfficeServiceId,
        public LicensePlateType $licensePlateType,
        public VehicleType $vehicleType,
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
    }

    /**
     * Convert the customization data to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'registrationOfficeServiceId' => $this->registrationOfficeServiceId,
            'licensePlateType' => $this->licensePlateType->value,
            'vehicleType' => $this->vehicleType->value,
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * Create an instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registrationOfficeServiceId: Hydrator::requireInt($data, 'registrationOfficeServiceId', self::CONTEXT),
            licensePlateType: Hydrator::requireEnum(LicensePlateType::class, $data, 'licensePlateType', self::CONTEXT),
            vehicleType: Hydrator::requireEnum(VehicleType::class, $data, 'vehicleType', self::CONTEXT),
            licensePlateNumberComponents: EuroLicensePlateNumberComponents::fromArray(
                Hydrator::requireArray($data, 'licensePlateNumberComponents', self::CONTEXT),
            ),
            seasonStartMonth: Hydrator::optionalInt($data, 'seasonStartMonth', self::CONTEXT),
            seasonEndMonth: Hydrator::optionalInt($data, 'seasonEndMonth', self::CONTEXT),
        );
    }
}
