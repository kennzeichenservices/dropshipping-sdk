<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of a license plate, including regular, electric, and historical variants
 * with optional seasonal designation.
 */
enum LicensePlateType: string
{
    case Regular = 'REGULAR';
    case RegularSeason = 'REGULAR_SEASON';
    case Electric = 'ELECTRIC';
    case ElectricSeason = 'ELECTRIC_SEASON';
    case Historical = 'HISTORICAL';
    case HistoricalSeason = 'HISTORICAL_SEASON';
}
