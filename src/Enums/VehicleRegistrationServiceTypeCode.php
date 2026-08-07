<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Identifies the kind of registration procedure (Zulassungsvorgang) to perform.
 *
 * The API spec lists these codes without descriptions. Only the codes whose
 * meaning is confirmed carry a comment below; the remaining ones are passed
 * through verbatim.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.4.0) and may change without a major version bump.
 */
enum VehicleRegistrationServiceTypeCode: string
{
    /** Neuzulassung — first registration of a vehicle. */
    case NZ = 'NZ';

    /** Wiederzulassung — re-registration of a deregistered vehicle. */
    case WZ = 'WZ';

    case UO = 'UO';
    case UI = 'UI';
    case UM = 'UM';
    case WG = 'WG';
    case UG = 'UG';
    case HA = 'HA';
}
