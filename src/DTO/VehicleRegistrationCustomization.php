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
 */
final readonly class VehicleRegistrationCustomization implements ItemCustomizationInterface
{
    /** @var ProductType The product type, always VehicleRegistration. */
    public ProductType $productType;

    /**
     * @param VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface $licensePlateNumberAssignmentStrategy How the license plate number is assigned. Carries the plate itself for the RESERVATION strategy.
     * @param VehicleRegistrationServiceTypeCode                               $vehicleRegistrationServiceTypeCode   The kind of registration procedure (Zulassungsvorgang).
     * @param bool                                                             $deregistered                          True if the vehicle is already deregistered, false otherwise.
     * @param VehicleRegistrationVehicleType                                   $vehicleType                           Type of vehicle being registered.
     * @param string                                                           $electronicInsuranceConfirmationNumber Electronic insurance confirmation number (eVB-Nummer), exactly 7 characters.
     * @param string                                                           $vehicleIdentificationNumber           Vehicle identification number (VIN / FIN).
     * @param string                                                           $vehicleTitleSecurityCode              Security code from Fahrzeugbrief / Zulassungsbescheinigung Teil II, exactly 12 characters.
     * @param string                                                           $iban                                  IBAN for the recurring vehicle tax debit.
     * @param string                                                           $bic                                   BIC belonging to the IBAN.
     * @param string|null                                                      $vehicleRegistrationCertificateSecurityCode Security code from Fahrzeugschein / Zulassungsbescheinigung Teil I, exactly 7 characters.
     * @param string|null                                                      $vehicleTitleNumber                    Number from Fahrzeugbrief / Zulassungsbescheinigung Teil II (Fahrzeugbriefnummer), exactly 8 characters.
     * @param VehicleRegistrationPreviousLicensePlate|null                     $previousLicensePlate                  The license plate the vehicle carried before, if any.
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
