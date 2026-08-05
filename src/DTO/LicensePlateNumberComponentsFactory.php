<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\LicensePlateUsageType;
use Dropshipping\Support\Hydrator;

/**
 * Factory for creating the appropriate LicensePlateNumberComponentsInterface implementation based on usage type.
 */
final class LicensePlateNumberComponentsFactory
{
    /**
     * Create a license plate number components instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): LicensePlateNumberComponentsInterface
    {
        $usageType = Hydrator::requireEnum(
            LicensePlateUsageType::class,
            $data,
            'usageType',
            'LicensePlateComponents',
        );

        return match ($usageType) {
            LicensePlateUsageType::Euro => EuroLicensePlateNumberComponents::fromArray($data),
            LicensePlateUsageType::Parking => ParkingLicensePlateNumberComponents::fromArray($data),
        };
    }
}
