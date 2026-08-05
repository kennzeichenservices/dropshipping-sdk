<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Support\Hydrator;

/**
 * The license plate assigned to a vehicle by a registration XKFZ webhook event.
 *
 * Season months are only meaningful for the `*_SEASON` plate types. They are taken
 * as sent — an inbound event is reported, not validated.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationXkfzEventLicensePlate
{
    private const CONTEXT = 'VehicleRegistrationXkfzEventLicensePlate';

    /**
     * @param EuroLicensePlateNumberComponents    $licensePlateNumberComponents The assigned plate number.
     * @param VehicleRegistrationLicensePlateType $licensePlateType             The type of plate assigned.
     * @param int|null                            $seasonStartMonth             First month of the season, if seasonal.
     * @param int|null                            $seasonEndMonth               Last month of the season, if seasonal.
     */
    public function __construct(
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public VehicleRegistrationLicensePlateType $licensePlateType,
        public ?int $seasonStartMonth,
        public ?int $seasonEndMonth,
    ) {
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            licensePlateNumberComponents: EuroLicensePlateNumberComponents::fromArray(
                Hydrator::requireArray($data, 'licensePlateNumberComponents', self::CONTEXT),
            ),
            licensePlateType: Hydrator::requireEnum(
                VehicleRegistrationLicensePlateType::class,
                $data,
                'licensePlateType',
                self::CONTEXT,
            ),
            seasonStartMonth: Hydrator::optionalInt($data, 'seasonStartMonth', self::CONTEXT),
            seasonEndMonth: Hydrator::optionalInt($data, 'seasonEndMonth', self::CONTEXT),
        );
    }
}
