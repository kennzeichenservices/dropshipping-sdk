<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Processing status of a durable power of attorney (Dauervollmacht).
 *
 * A freshly created power of attorney starts as INCOMPLETE and moves to COMPLETE
 * once its documents are generated and signed, or to ERROR when generation or
 * signature fail. Both transitions are also announced as webhook events,
 * VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION and
 * VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_ERROR.
 *
 * Whether a power of attorney was revoked is not a status but the separate
 * `active` flag.
 */
enum VehicleRegistrationDurablePowerOfAttorneyStatus: string
{
    /** Initial status, waiting for document generation and signature. */
    case Incomplete = 'INCOMPLETE';

    /** Documents generated and signed. */
    case Complete = 'COMPLETE';

    /** Document generation or signature failed, or another error occurred. */
    case Error = 'ERROR';

    /**
     * Placeholder for a status this SDK version does not know.
     *
     * Never sent by the API. Assigned locally during hydration, so a status
     * added later does not make the whole listing fail.
     */
    case Unknown = 'UNKNOWN';
}
