<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Identifies the kind of registration procedure (Zulassungsvorgang) to perform.
 *
 * The API spec lists these codes without descriptions, and without a word on
 * which fields each one requires or forbids. The comments and the two
 * predicates below carry what is known from outside the spec — see the "Rules
 * the API enforces but does not publish" section on
 * {@see \Dropshipping\DTO\VehicleRegistrationCustomization} for where that
 * knowledge comes from and what is done with it.
 *
 * The codes fall into four groups. What distinguishes the members of a group
 * from one another is not documented anywhere we have access to; their field
 * requirements are identical.
 */
enum VehicleRegistrationServiceTypeCode: string
{
    /** Neuzulassung — first registration of a vehicle. */
    case NZ = 'NZ';

    /** Wiederzulassung — re-registration of a deregistered vehicle. */
    case WZ = 'WZ';

    /** Umschreibung. */
    case UO = 'UO';

    /** Umschreibung. */
    case UI = 'UI';

    /** Umschreibung. */
    case UM = 'UM';

    /** Wiederzulassung, like {@see self::WZ}. */
    case WG = 'WG';

    /** Umschreibung. */
    case UG = 'UG';

    /** Halteränderung — the plate stays with the vehicle. */
    case HA = 'HA';

    /**
     * Whether the procedure continues a registration the vehicle already had.
     *
     * True for every code but `NZ`: only a first registration has neither a
     * Zulassungsbescheinigung Teil I nor a plate the vehicle carried before.
     * The two fields carrying those are required exactly when this is true,
     * and forbidden when it is false.
     */
    public function requiresPreviousRegistration(): bool
    {
        return match ($this) {
            self::NZ => false,
            self::WZ, self::WG, self::UG, self::UM, self::UO, self::UI, self::HA => true,
        };
    }

    /**
     * Whether the procedure requires the vehicle to be deregistered already.
     *
     * A Neuzulassung has never been registered, and a Wiederzulassung picks a
     * deregistered vehicle back up. For the remaining codes the state of the
     * vehicle is not constrained.
     */
    public function requiresDeregisteredVehicle(): bool
    {
        return match ($this) {
            self::NZ, self::WZ, self::WG => true,
            self::UG, self::UM, self::UO, self::UI, self::HA => false,
        };
    }
}
