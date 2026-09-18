<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

/**
 * The delivery configurations of a vehicle registration: where each of the two
 * registration documents goes once the office has issued it.
 *
 * The API requires both configurations together — there is no way to redirect
 * only the Zulassungsbescheinigung Teil II and leave Teil I unmentioned. Pass
 * {@see VehicleRegistrationDeliveryToOwnerDeliveryConfiguration} for the
 * document that keeps the default.
 *
 * Omitting {@see VehicleRegistrationCustomization::$deliveryConfigurations}
 * altogether sends both documents to the vehicle holder.
 */
final readonly class VehicleRegistrationDeliveryConfigurations
{
    /**
     * @param VehicleRegistrationDeliveryConfigurationInterface $vehicleRegistrationCertificate Where the Zulassungsbescheinigung Teil I (Fahrzeugschein) goes.
     * @param VehicleRegistrationDeliveryConfigurationInterface $vehicleTitle                   Where the Zulassungsbescheinigung Teil II (Fahrzeugbrief) goes.
     */
    public function __construct(
        public VehicleRegistrationDeliveryConfigurationInterface $vehicleRegistrationCertificate,
        public VehicleRegistrationDeliveryConfigurationInterface $vehicleTitle,
    ) {
    }

    /**
     * Convert the delivery configurations to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'vehicleRegistrationCertificate' => $this->vehicleRegistrationCertificate->toArray(),
            'vehicleTitle' => $this->vehicleTitle->toArray(),
        ];
    }
}
