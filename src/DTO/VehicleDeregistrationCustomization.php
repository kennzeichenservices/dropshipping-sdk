<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleDeregistrationLicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use Dropshipping\Support\Validator;

/**
 * Data transfer object representing the customization for a vehicle deregistration order item.
 *
 * Implements {@see ItemCustomizationInterface} and automatically sets the
 * product type to {@see ProductType::VehicleDeregistration}.
 */
final readonly class VehicleDeregistrationCustomization implements ItemCustomizationInterface
{
    /** @var ProductType The product type, always VehicleDeregistration. */
    public ProductType $productType;

    /**
     * @param VehicleDeregistrationVehicleType       $vehicleType                            Type of vehicle being deregistered.
     * @param VehicleDeregistrationLicensePlateType   $licensePlateType                       Type of license plate on the vehicle.
     * @param EuroLicensePlateNumberComponents        $licensePlateNumberComponents            License plate number components.
     * @param bool                                    $licensePlateReservationIncluded         Whether license plate reservation is included.
     * @param string                                  $vehicleIdentificationNumber             Vehicle identification number (VIN).
     * @param string                                  $vehicleRegistrationCertificateSecurityCode Security code from the registration certificate.
     * @param string                                  $vehicleRegistrationDate                 Vehicle registration date in ISO 8601 format.
     * @param string                                  $rearLicensePlateSecurityCode            Security code from the rear license plate.
     * @param string|null                             $frontLicensePlateSecurityCode           Security code from the front license plate, if available.
     * @param int|null                                $seasonStartMonth                        Start month for seasonal plates (1–12).
     * @param int|null                                $seasonEndMonth                          End month for seasonal plates (1–12).
     */
    public function __construct(
        public VehicleDeregistrationVehicleType $vehicleType,
        public VehicleDeregistrationLicensePlateType $licensePlateType,
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public bool $licensePlateReservationIncluded,
        public string $vehicleIdentificationNumber,
        public string $vehicleRegistrationCertificateSecurityCode,
        public string $vehicleRegistrationDate,
        public string $rearLicensePlateSecurityCode,
        public ?string $frontLicensePlateSecurityCode = null,
        public ?int $seasonStartMonth = null,
        public ?int $seasonEndMonth = null,
    ) {
        $this->productType = ProductType::VehicleDeregistration;

        Validator::requireNonEmpty($vehicleIdentificationNumber, 'vehicleIdentificationNumber');
        Validator::requireNonEmpty($vehicleRegistrationCertificateSecurityCode, 'vehicleRegistrationCertificateSecurityCode');
        Validator::requireNonEmpty($vehicleRegistrationDate, 'vehicleRegistrationDate');
        Validator::requireNonEmpty($rearLicensePlateSecurityCode, 'rearLicensePlateSecurityCode');
        Validator::requireNullableStringLength($frontLicensePlateSecurityCode, 'frontLicensePlateSecurityCode', 1, 255);
        Validator::requireNullableIntRange($seasonStartMonth, 'seasonStartMonth', 1, 12);
        Validator::requireNullableIntRange($seasonEndMonth, 'seasonEndMonth', 1, 12);
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
            'vehicleType' => $this->vehicleType->value,
            'licensePlateType' => $this->licensePlateType->value,
            'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
            'licensePlateReservationIncluded' => $this->licensePlateReservationIncluded,
            'vehicleIdentificationNumber' => $this->vehicleIdentificationNumber,
            'vehicleRegistrationCertificateSecurityCode' => $this->vehicleRegistrationCertificateSecurityCode,
            'vehicleRegistrationDate' => $this->vehicleRegistrationDate,
            'rearLicensePlateSecurityCode' => $this->rearLicensePlateSecurityCode,
            'frontLicensePlateSecurityCode' => $this->frontLicensePlateSecurityCode,
            'seasonStartMonth' => $this->seasonStartMonth,
            'seasonEndMonth' => $this->seasonEndMonth,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
