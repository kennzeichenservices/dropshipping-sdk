<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of license plate for a vehicle registration request.
 *
 * Separate enum from {@see LicensePlateType} because the API spec defines
 * distinct license plate type schemas per context.
 */
enum VehicleRegistrationLicensePlateType: string
{
    case Regular = 'REGULAR';
    case RegularSeason = 'REGULAR_SEASON';
    case Electric = 'ELECTRIC';
    case ElectricSeason = 'ELECTRIC_SEASON';
    case Historical = 'HISTORICAL';
    case HistoricalSeason = 'HISTORICAL_SEASON';
}
