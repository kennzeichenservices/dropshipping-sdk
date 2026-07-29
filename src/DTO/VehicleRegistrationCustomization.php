<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategy;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
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
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationCustomization implements ItemCustomizationInterface
{
    /** @var ProductType The product type, always VehicleRegistration. */
    public ProductType $productType;

    /**
     * @param VehicleRegistrationLicensePlateNumberAssignmentStrategy $licensePlateNumberAssignmentStrategy How the license plate number is assigned.
     * @param VehicleRegistrationServiceTypeCode                      $vehicleRegistrationServiceTypeCode   The kind of registration procedure (Zulassungsvorgang).
     * @param EuroLicensePlateNumberComponents                        $licensePlateNumberComponents         License plate number components.
     * @param bool                                                    $deregistered                          True if the vehicle is already deregistered, false otherwise.
     * @param VehicleRegistrationVehicleType                          $vehicleType                           Type of vehicle being registered.
     * @param VehicleRegistrationLicensePlateType                     $licensePlateType                      Type of license plate to issue.
     * @param string                                                  $electronicInsuranceConfirmationNumber Electronic insurance confirmation number (eVB-Nummer), exactly 7 characters.
     * @param string                                                  $vehicleIdentificationNumber           Vehicle identification number (VIN / FIN).
     * @param string                                                  $vehicleTitleSecurityCode              Security code from Fahrzeugbrief / Zulassungsbescheinigung Teil II, exactly 12 characters.
     * @param string                                                  $iban                                  IBAN for the recurring vehicle tax debit.
     * @param string                                                  $bic                                   BIC belonging to the IBAN.
     * @param int|null                                                $seasonStartMonth                      Start month for seasonal plates (1–12).
     * @param int|null                                                $seasonEndMonth                        End month for seasonal plates (1–12).
     * @param string|null                                             $vehicleRegistrationCertificateSecurityCode Security code from Fahrzeugschein / Zulassungsbescheinigung Teil I, exactly 7 characters.
     * @param string|null                                             $vehicleTitleNumber                    Number from Fahrzeugbrief / Zulassungsbescheinigung Teil II (Fahrzeugbriefnummer), exactly 8 characters.
     * @param VehicleRegistrationPreviousLicensePlate|null            $previousLicensePlate                  The license plate the vehicle carried before, if any.
     * @param string|null                                             $reservationPin                        PIN of a previously reserved license plate number. Required when the assignment strategy is RESERVATION.
     *
     * @throws DropshippingException When a value violates the API constraints, or when
     *                               the RESERVATION strategy is used without a reservation PIN.
     */
    public function __construct(
        public VehicleRegistrationLicensePlateNumberAssignmentStrategy $licensePlateNumberAssignmentStrategy,
        public VehicleRegistrationServiceTypeCode $vehicleRegistrationServiceTypeCode,
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public bool $deregistered,
        public VehicleRegistrationVehicleType $vehicleType,
        public VehicleRegistrationLicensePlateType $licensePlateType,
        public string $electronicInsuranceConfirmationNumber,
        public string $vehicleIdentificationNumber,
        public string $vehicleTitleSecurityCode,
        public string $iban,
        public string $bic,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
        public ?string $vehicleRegistrationCertificateSecurityCode = null,
        public ?string $vehicleTitleNumber = null,
        public ?VehicleRegistrationPreviousLicensePlate $previousLicensePlate = null,
        public ?string $reservationPin = null,
    ) {
        $this->productType = ProductType::VehicleRegistration;

        Validator::requireStringLength($electronicInsuranceConfirmationNumber, 'electronicInsuranceConfirmationNumber', 7, 7);
        Validator::requireStringLength($vehicleIdentificationNumber, 'vehicleIdentificationNumber', 4, 17);
        Validator::requireStringLength($vehicleTitleSecurityCode, 'vehicleTitleSecurityCode', 12, 12);
        Validator::requireStringLength($iban, 'iban', 15, 34);
        Validator::requireStringLength($bic, 'bic', 8, 11);
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
        Validator::requireNullableStringLength($vehicleRegistrationCertificateSecurityCode, 'vehicleRegistrationCertificateSecurityCode', 7, 7);
        Validator::requireNullableStringLength($vehicleTitleNumber, 'vehicleTitleNumber', 8, 8);
        Validator::requireNullableStringLength($reservationPin, 'reservationPin', 4, 12);

        if ($licensePlateNumberAssignmentStrategy === VehicleRegistrationLicensePlateNumberAssignmentStrategy::Reservation
            && $reservationPin === null
        ) {
            throw new DropshippingException(
                'reservationPin is required when licensePlateNumberAssignmentStrategy is RESERVATION',
            );
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
            'licensePlateNumberAssignmentStrategy' => $this->licensePlateNumberAssignmentStrategy->value,
            'vehicleRegistrationServiceTypeCode' => $this->vehicleRegistrationServiceTypeCode->value,
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'deregistered' => $this->deregistered,
            'vehicleType' => $this->vehicleType->value,
            'licensePlateType' => $this->licensePlateType->value,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
            'electronicInsuranceConfirmationNumber' => $this->electronicInsuranceConfirmationNumber,
            'vehicleIdentificationNumber' => $this->vehicleIdentificationNumber,
            'vehicleRegistrationCertificateSecurityCode' => $this->vehicleRegistrationCertificateSecurityCode,
            'vehicleTitleNumber' => $this->vehicleTitleNumber,
            'vehicleTitleSecurityCode' => $this->vehicleTitleSecurityCode,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'previousLicensePlate' => $this->previousLicensePlate?->toArray(),
            'reservationPin' => $this->reservationPin,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
