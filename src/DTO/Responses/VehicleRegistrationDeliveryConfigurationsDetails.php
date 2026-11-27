<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Where the registration documents go, as the API returns it with a durable
 * power of attorney.
 *
 * The read-side counterpart of
 * {@see \Dropshipping\DTO\VehicleRegistrationDeliveryConfigurations}. It is a
 * separate class so that reading does not run the request validation, and both
 * documents are nullable although the spec requires them: an incomplete record
 * must not take the whole listing down.
 */
final readonly class VehicleRegistrationDeliveryConfigurationsDetails
{
    private const CONTEXT = 'VehicleRegistrationDeliveryConfigurationsDetails';

    /**
     * @param VehicleRegistrationDeliveryConfigurationDetails|null $vehicleRegistrationCertificate Where the Zulassungsbescheinigung Teil I (Fahrzeugschein) goes.
     * @param VehicleRegistrationDeliveryConfigurationDetails|null $vehicleTitle                   Where the Zulassungsbescheinigung Teil II (Fahrzeugbrief) goes.
     */
    public function __construct(
        public ?VehicleRegistrationDeliveryConfigurationDetails $vehicleRegistrationCertificate = null,
        public ?VehicleRegistrationDeliveryConfigurationDetails $vehicleTitle = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $certificate = Hydrator::optionalArray($data, 'vehicleRegistrationCertificate', self::CONTEXT);
        $title = Hydrator::optionalArray($data, 'vehicleTitle', self::CONTEXT);

        return new self(
            vehicleRegistrationCertificate: $certificate === null ? null : VehicleRegistrationDeliveryConfigurationDetails::fromArray($certificate),
            vehicleTitle: $title === null ? null : VehicleRegistrationDeliveryConfigurationDetails::fromArray($title),
        );
    }
}
