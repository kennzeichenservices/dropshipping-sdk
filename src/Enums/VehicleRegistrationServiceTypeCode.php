<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Identifies the kind of registration procedure (Zulassungsvorgang) to perform.
 *
 * The API spec lists these codes without descriptions. Up to 2.4.0 it said
 * nothing about which fields each one requires or forbids; 2.5.0 spells out the
 * rules for `HA` in the field descriptions, the rest is still known only from
 * outside the spec. The predicates below carry both — see the "Rules per
 * service type code" section on
 * {@see \Dropshipping\DTO\VehicleRegistrationCustomization} for where each
 * rule comes from and what is done with it.
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

    /**
     * Halteränderung — only the holder changes. The vehicle stays registered,
     * keeps its plate, and its Zulassungsbescheinigung Teil II is not involved.
     */
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

    /**
     * Whether the procedure requires the vehicle to still be registered.
     *
     * Only a Halteränderung: it hands a running registration to a new holder,
     * so `deregistered` must be false (spec 2.5.0).
     */
    public function requiresRegisteredVehicle(): bool
    {
        return match ($this) {
            self::HA => true,
            self::NZ, self::WZ, self::WG, self::UG, self::UM, self::UO, self::UI => false,
        };
    }

    /**
     * Whether the procedure keeps the plate the vehicle already carries.
     *
     * Only a Halteränderung, which must use the RETAINMENT strategy (spec
     * 2.5.0). The other codes may still choose RETAINMENT — every one but `NZ`,
     * which has no plate to keep.
     */
    public function retainsLicensePlate(): bool
    {
        return match ($this) {
            self::HA => true,
            self::NZ, self::WZ, self::WG, self::UG, self::UM, self::UO, self::UI => false,
        };
    }

    /**
     * Whether the procedure involves the Zulassungsbescheinigung Teil II.
     *
     * True for every code but `HA`. Where true, `vehicleTitleSecurityCode` is
     * required; where false, both `vehicleTitleSecurityCode` and
     * `vehicleTitleNumber` must be null (spec 2.5.0).
     */
    public function involvesVehicleTitle(): bool
    {
        return match ($this) {
            self::HA => false,
            self::NZ, self::WZ, self::WG, self::UG, self::UM, self::UO, self::UI => true,
        };
    }

    /**
     * Whether the procedure requires the insurance and vehicle tax details.
     *
     * True for every code but `HA`: the eVB number, IBAN and BIC are required
     * then. For `HA` the spec (2.5.0) only drops the requirement — it does not
     * forbid the fields, so they are passed on when given.
     */
    public function requiresInsuranceAndTaxDetails(): bool
    {
        return match ($this) {
            self::HA => false,
            self::NZ, self::WZ, self::WG, self::UG, self::UM, self::UO, self::UI => true,
        };
    }
}
