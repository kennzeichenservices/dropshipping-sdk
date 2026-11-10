<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Data transfer object representing the customization for a vehicle registration order item.
 *
 * Implements {@see ItemCustomizationInterface} and automatically sets the
 * product type to {@see ProductType::VehicleRegistration}.
 *
 * ## Rules per service type code
 *
 * `VehicleRegistrationServiceTypeCode` is a bare enum block in the spec. What
 * each code allows is only hinted at in the field descriptions: since 2.4.0
 * they name the `NZ` side, and 2.5.0 adds the rules for `HA`. Everything else
 * is known from API rejections and the field requirement table of the
 * registration request the platform builds from these values. The API rejects
 * violations only after identification and QES have already run, which makes
 * learning these rules by rejection expensive. All of them are therefore
 * enforced here, at construction time.
 *
 * Partly in the spec (the `NZ` side), the rest from rejections:
 *
 * - `NZ` (Neuzulassung) forbids `vehicleRegistrationCertificateSecurityCode`
 *   and `previousLicensePlate`. A factory-new vehicle has no
 *   Zulassungsbescheinigung Teil I yet — it is issued by this very
 *   registration — and no plate it carried before. The API reports the first
 *   one as `verificationCode must be null`; `verificationCode` is its internal
 *   name for the field the spec calls
 *   `vehicleRegistrationCertificateSecurityCode`.
 * - Every other code *requires* both, for the mirror image of that reason: they
 *   all continue an existing registration, which the ZB I security code and the
 *   previous plate identify. See
 *   {@see VehicleRegistrationServiceTypeCode::requiresPreviousRegistration()}.
 *   2.5.0 confirms this for `HA`.
 * - `NZ`, `WZ` and `WG` require `deregistered` to be true — none of them can run
 *   on a vehicle that is currently registered. See
 *   {@see VehicleRegistrationServiceTypeCode::requiresDeregisteredVehicle()}.
 * - The `RETAINMENT` strategy needs a `previousLicensePlate` to take the number
 *   from, and forbids the two security codes on it: the plates stay on the
 *   vehicle, so nobody scratches their seals off to read a code.
 *
 * Published in 2.5.0, all for `HA` (Halteränderung), where only the holder
 * changes and the vehicle keeps its registration and its plate:
 *
 * - `deregistered` must be false. See
 *   {@see VehicleRegistrationServiceTypeCode::requiresRegisteredVehicle()}.
 * - The strategy must be `RETAINMENT`. See
 *   {@see VehicleRegistrationServiceTypeCode::retainsLicensePlate()}.
 * - `vehicleTitleNumber` and `vehicleTitleSecurityCode` must be null; every
 *   other code requires `vehicleTitleSecurityCode`. See
 *   {@see VehicleRegistrationServiceTypeCode::involvesVehicleTitle()}.
 * - `electronicInsuranceConfirmationNumber`, `iban` and `bic` are optional;
 *   every other code requires them. See
 *   {@see VehicleRegistrationServiceTypeCode::requiresInsuranceAndTaxDetails()}.
 *
 * Published in 2.7.0, both about the vehicle tax debit:
 *
 * - `bic` is only required where the `iban` belongs to Monaco, San Marino or
 *   Switzerland, and optional for every other country. Up to 2.6.0 the spec
 *   called it required for every code but `HA`, which this class enforced; that
 *   guard is gone, because rejecting a request the API accepts is the worse of
 *   the two errors.
 * - `iban` and `bic` must both be empty when the vehicle holder is a legal
 *   person carrying a `customsMajorCustomerId`, which settles the tax through
 *   the customs administration instead of a direct debit mandate. Whether
 *   `iban` is required or forbidden therefore depends on the holder, which this
 *   class does not see, so both rules are enforced by
 *   {@see \Dropshipping\DTO\Requests\VehicleRegistrationRequest} where the
 *   holder and the customization meet. Building a customization without an IBAN
 *   for a code that needs one no longer throws here.
 *
 * Should the API ever accept other combinations, the guards here have to go
 * with them.
 */
final readonly class VehicleRegistrationCustomization implements ItemCustomizationInterface
{
    /**
     * Country prefixes whose IBAN needs a BIC alongside it (2.7.0): Monaco, San
     * Marino and Switzerland are in SEPA but outside the EU, so the debit is not
     * routed on the IBAN alone.
     */
    private const BIC_REQUIRING_IBAN_PREFIXES = ['MC', 'SM', 'CH'];

    /** @var ProductType The product type, always VehicleRegistration. */
    public ProductType $productType;

    /**
     * @param VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface $licensePlateNumberAssignmentStrategy How the license plate number is assigned. Carries the plate itself for the RESERVATION strategy.
     * @param VehicleRegistrationServiceTypeCode                               $vehicleRegistrationServiceTypeCode   The kind of registration procedure (Zulassungsvorgang).
     * @param bool                                                             $deregistered                          True if the vehicle is already deregistered, false otherwise. Must be true for service type codes NZ, WZ and WG, and false for HA.
     * @param VehicleRegistrationVehicleType                                   $vehicleType                           Type of vehicle being registered.
     * @param string|null                                                      $electronicInsuranceConfirmationNumber Electronic insurance confirmation number (eVB-Nummer), exactly 7 characters. Required for every service type code but HA.
     * @param string                                                           $vehicleIdentificationNumber           Vehicle identification number (VIN / FIN).
     * @param string|null                                                      $vehicleTitleSecurityCode              Security code from Fahrzeugbrief / Zulassungsbescheinigung Teil II, exactly 12 characters. Required for every service type code but HA, which forbids it.
     * @param string|null                                                      $iban                                  IBAN for the recurring vehicle tax debit. Required for every service type code but HA, unless the vehicle holder is a legal person settling the tax through the customs administration, which forbids it. Both rules are checked by {@see \Dropshipping\DTO\Requests\VehicleRegistrationRequest}.
     * @param string|null                                                      $bic                                   BIC belonging to the IBAN. Required only for an IBAN from Monaco, San Marino or Switzerland (2.7.0), optional otherwise.
     * @param string|null                                                      $vehicleRegistrationCertificateSecurityCode Security code from Fahrzeugschein / Zulassungsbescheinigung Teil I, exactly 7 characters. Required for every service type code but NZ, which forbids it.
     * @param string|null                                                      $vehicleTitleNumber                    Number from Fahrzeugbrief / Zulassungsbescheinigung Teil II (Fahrzeugbriefnummer), exactly 8 characters. Forbidden for HA.
     * @param VehicleRegistrationPreviousLicensePlate|null                     $previousLicensePlate                  The license plate the vehicle carried before. Required for every service type code but NZ, which forbids it, and by the RETAINMENT strategy.
     * @param VehicleRegistrationDeliveryConfigurations|null                   $deliveryConfigurations                Where the two registration documents are delivered. Omitted, both go to the vehicle holder.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface $licensePlateNumberAssignmentStrategy,
        public VehicleRegistrationServiceTypeCode $vehicleRegistrationServiceTypeCode,
        public bool $deregistered,
        public VehicleRegistrationVehicleType $vehicleType,
        public ?string $electronicInsuranceConfirmationNumber,
        public string $vehicleIdentificationNumber,
        public ?string $vehicleTitleSecurityCode,
        public ?string $iban,
        public ?string $bic,
        public ?string $vehicleRegistrationCertificateSecurityCode = null,
        public ?string $vehicleTitleNumber = null,
        public ?VehicleRegistrationPreviousLicensePlate $previousLicensePlate = null,
        public ?VehicleRegistrationDeliveryConfigurations $deliveryConfigurations = null,
    ) {
        $this->productType = ProductType::VehicleRegistration;

        Validator::requireNullableStringLength($electronicInsuranceConfirmationNumber, 'electronicInsuranceConfirmationNumber', 7, 7);
        Validator::requireStringLength($vehicleIdentificationNumber, 'vehicleIdentificationNumber', 4, 17);
        Validator::requireNullableStringLength($vehicleTitleSecurityCode, 'vehicleTitleSecurityCode', 12, 12);
        Validator::requireNullableStringLength($iban, 'iban', 15, 34);
        Validator::requireNullableStringLength($bic, 'bic', 8, 11);
        Validator::requireNullableStringLength($vehicleRegistrationCertificateSecurityCode, 'vehicleRegistrationCertificateSecurityCode', 7, 7);
        Validator::requireNullableStringLength($vehicleTitleNumber, 'vehicleTitleNumber', 8, 8);

        $forCode = sprintf('for vehicleRegistrationServiceTypeCode %s', $vehicleRegistrationServiceTypeCode->value);

        if ($vehicleRegistrationServiceTypeCode->requiresPreviousRegistration()) {
            Validator::requireNotNull($vehicleRegistrationCertificateSecurityCode, 'vehicleRegistrationCertificateSecurityCode', $forCode);
            Validator::requireNotNull($previousLicensePlate, 'previousLicensePlate', $forCode);
        } else {
            Validator::requireNull($vehicleRegistrationCertificateSecurityCode, 'vehicleRegistrationCertificateSecurityCode', $forCode);
            Validator::requireNull($previousLicensePlate, 'previousLicensePlate', $forCode);
        }

        if ($vehicleRegistrationServiceTypeCode->requiresDeregisteredVehicle()) {
            Validator::requireTrue($deregistered, 'deregistered', $forCode);
        }

        if ($vehicleRegistrationServiceTypeCode->requiresRegisteredVehicle()) {
            Validator::requireFalse($deregistered, 'deregistered', $forCode);
        }

        if (
            $vehicleRegistrationServiceTypeCode->retainsLicensePlate()
            && !$licensePlateNumberAssignmentStrategy instanceof VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained
        ) {
            throw new DropshippingException(sprintf(
                'Field "licensePlateNumberAssignmentStrategy" must be %s %s, got %s',
                VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Retainment->value,
                $forCode,
                $licensePlateNumberAssignmentStrategy->strategyType()->value,
            ));
        }

        if ($vehicleRegistrationServiceTypeCode->involvesVehicleTitle()) {
            Validator::requireNotNull($vehicleTitleSecurityCode, 'vehicleTitleSecurityCode', $forCode);
        } else {
            Validator::requireNull($vehicleTitleSecurityCode, 'vehicleTitleSecurityCode', $forCode);
            Validator::requireNull($vehicleTitleNumber, 'vehicleTitleNumber', $forCode);
        }

        if ($vehicleRegistrationServiceTypeCode->requiresInsuranceAndTaxDetails()) {
            Validator::requireNotNull($electronicInsuranceConfirmationNumber, 'electronicInsuranceConfirmationNumber', $forCode);
        }

        if ($iban !== null && self::ibanRequiresBic($iban)) {
            Validator::requireNotNull($bic, 'bic', sprintf('for an iban starting with %s', mb_substr($iban, 0, 2)));
        }

        if ($licensePlateNumberAssignmentStrategy instanceof VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained) {
            $whenRetained = 'when the previous license plate number is retained';

            Validator::requireNotNull($previousLicensePlate, 'previousLicensePlate', $whenRetained);
            Validator::requireNull($previousLicensePlate?->frontLicensePlateSecurityCode, 'previousLicensePlate.frontLicensePlateSecurityCode', $whenRetained);
            Validator::requireNull($previousLicensePlate?->rearLicensePlateSecurityCode, 'previousLicensePlate.rearLicensePlateSecurityCode', $whenRetained);
        }
    }

    /**
     * Whether an IBAN has to be accompanied by a BIC.
     *
     * True for Monaco, San Marino and Switzerland. The country is read from the
     * first two characters, upper cased first so a lower case IBAN is judged the
     * same way; the API itself rejects a malformed IBAN.
     */
    private static function ibanRequiresBic(string $iban): bool
    {
        return in_array(mb_strtoupper(mb_substr($iban, 0, 2)), self::BIC_REQUIRING_IBAN_PREFIXES, true);
    }

    /**
     * Convert the customization to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'productType' => $this->productType->value,
            'licensePlateNumberAssignmentStrategy' => $this->licensePlateNumberAssignmentStrategy->toArray(),
            'vehicleRegistrationServiceTypeCode' => $this->vehicleRegistrationServiceTypeCode->value,
            'deregistered' => $this->deregistered,
            'vehicleType' => $this->vehicleType->value,
            'electronicInsuranceConfirmationNumber' => $this->electronicInsuranceConfirmationNumber,
            'vehicleIdentificationNumber' => $this->vehicleIdentificationNumber,
            'vehicleRegistrationCertificateSecurityCode' => $this->vehicleRegistrationCertificateSecurityCode,
            'vehicleTitleNumber' => $this->vehicleTitleNumber,
            'vehicleTitleSecurityCode' => $this->vehicleTitleSecurityCode,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'previousLicensePlate' => $this->previousLicensePlate?->toArray(),
            'deliveryConfigurations' => $this->deliveryConfigurations?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
