<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\LicensePlateUsageType;
use Dropshipping\Exceptions\DropshippingException;

final class LicensePlateNumberComponentsFactory
{
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): LicensePlateNumberComponentsInterface
    {
        $usageType = LicensePlateUsageType::from($data['usageType']);

        return match ($usageType) {
            LicensePlateUsageType::Euro => EuroLicensePlateNumberComponents::fromArray($data),
            LicensePlateUsageType::Parking => ParkingLicensePlateNumberComponents::fromArray($data),
        };
    }
}
