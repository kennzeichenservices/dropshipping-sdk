<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;
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
 * ## Rules the API enforces but does not publish
 *
 * Which fields a `vehicleRegistrationServiceTypeCode` allows is not part of any
 * spec up to 2.4.0 — `VehicleRegistrationServiceTypeCode` is a bare enum block
 * there, and every field below is declared plainly `nullable`. The API still
 * rejects violations, and only after identification and QES have already run,
 * which makes learning these rules by rejection expensive. The ones we know are
 * therefore enforced here, at construction time:
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
 * - `NZ`, `WZ` and `WG` require `deregistered` to be true — none of them can run
 *   on a vehicle that is currently registered. See
 *   {@see VehicleRegistrationServiceTypeCode::requiresDeregisteredVehicle()}.
 * - The `RETAINMENT` strategy needs a `previousLicensePlate` to take the number
 *   from, and forbids the two security codes on it: the plates stay on the
 *   vehicle, so nobody scratches their seals off to read a code.
 *
 * The sources are API rejections and the field requirement table of the
 * registration request the platform builds from these values, neither of which
 * is part of the published spec. Should the API ever accept these combinations,
 * the guards here have to go with them.
 */
final readonly class VehicleRegistrationCustomization implements ItemCustomizationInterface
{
    /** @var ProductType The product type, always VehicleRegistration. */
    public ProductType $productType;

    /**
     * @param VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface $licensePlateNumberAssignmentStrategy How the license plate number is assigned. Carries the plate itself for the RESERVATION strategy.
     * @param VehicleRegistrationServiceTypeCode                               $vehicleRegistrationServiceTypeCode   The kind of registration procedure (Zulassungsvorgang).
     * @param bool                                                             $deregistered                          True if the vehicle is already deregistered, false otherwise. Must be true for service type codes NZ, WZ and WG.
     * @param VehicleRegistrationVehicleType                                   $vehicleType                           Type of vehicle being registered.
     * @param string                                                           $electronicInsuranceConfirmationNumber Electronic insurance confirmation number (eVB-Nummer), exactly 7 characters.
     * @param string                                                           $vehicleIdentificationNumber           Vehicle identification number (VIN / FIN).
     * @param string                                                           $vehicleTitleSecurityCode              Security code from Fahrzeugbrief / Zulassungsbescheinigung Teil II, exactly 12 characters.
     * @param string                                                           $iban                                  IBAN for the recurring vehicle tax debit.
     * @param string                                                           $bic                                   BIC belonging to the IBAN.
     * @param string|null                                                      $vehicleRegistrationCertificateSecurityCode Security code from Fahrzeugschein / Zulassungsbescheinigung Teil I, exactly 7 characters. Required for every service type code but NZ, which forbids it.
     * @param string|null                                                      $vehicleTitleNumber                    Number from Fahrzeugbrief / Zulassungsbescheinigung Teil II (Fahrzeugbriefnummer), exactly 8 characters.
     * @param VehicleRegistrationPreviousLicensePlate|null                     $previousLicensePlate                  The license plate the vehicle carried before. Required for every service type code but NZ, which forbids it, and by the RETAINMENT strategy.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface $licensePlateNumberAssignmentStrategy,
        public VehicleRegistrationServiceTypeCode $vehicleRegistrationServiceTypeCode,
        public bool $deregistered,
        public VehicleRegistrationVehicleType $vehicleType,
        public string $electronicInsuranceConfirmationNumber,
        public string $vehicleIdentificationNumber,
        public string $vehicleTitleSecurityCode,
        public string $iban,
        public string $bic,
        public ?string $vehicleRegistrationCertificateSecurityCode = null,
        public ?string $vehicleTitleNumber = null,
        public ?VehicleRegistrationPreviousLicensePlate $previousLicensePlate = null,
    ) {
        $this->productType = ProductType::VehicleRegistration;

        Validator::requireStringLength($electronicInsuranceConfirmationNumber, 'electronicInsuranceConfirmationNumber', 7, 7);
        Validator::requireStringLength($vehicleIdentificationNumber, 'vehicleIdentificationNumber', 4, 17);
        Validator::requireStringLength($vehicleTitleSecurityCode, 'vehicleTitleSecurityCode', 12, 12);
        Validator::requireStringLength($iban, 'iban', 15, 34);
        Validator::requireStringLength($bic, 'bic', 8, 11);
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

        if ($licensePlateNumberAssignmentStrategy instanceof VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained) {
            $whenRetained = 'when the previous license plate number is retained';

            Validator::requireNotNull($previousLicensePlate, 'previousLicensePlate', $whenRetained);
            Validator::requireNull($previousLicensePlate?->frontLicensePlateSecurityCode, 'previousLicensePlate.frontLicensePlateSecurityCode', $whenRetained);
            Validator::requireNull($previousLicensePlate?->rearLicensePlateSecurityCode, 'previousLicensePlate.rearLicensePlateSecurityCode', $whenRetained);
        }
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
        ], static fn (mixed $value): bool => $value !== null);
    }
}
