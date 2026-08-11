<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleRegistrationApplicationFilePurposeType;
use Dropshipping\Support\Hydrator;

/**
 * Represents a signed application document attached to a
 * VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED webhook event.
 *
 * Pass the {@see $fileAccessKey} to
 * {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::downloadApplicationFileContent()}
 * to fetch the content, before {@see $expirationTime} passes. Note that this is a
 * different operation from the one serving {@see VehicleRegistrationXkfzEventFile};
 * the two key namespaces are not interchangeable.
 *
 * Treat that key as opaque. Its internal structure is going to change, and nothing
 * in it is meant to be parsed, validated or split by consumers.
 */
final readonly class VehicleRegistrationApplicationFile
{
    private const CONTEXT = 'VehicleRegistrationApplicationFile';

    /**
     * @param VehicleRegistrationApplicationFilePurposeType $purposeType    The purpose of the file.
     * @param string                                       $mediaType      MIME type of the file.
     * @param string                                       $fileAccessKey  Opaque key identifying the file content.
     * @param string                                       $expirationTime ISO 8601 datetime until which the file access key is valid.
     */
    public function __construct(
        public VehicleRegistrationApplicationFilePurposeType $purposeType,
        public string $mediaType,
        public string $fileAccessKey,
        public string $expirationTime,
    ) {
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            purposeType: Hydrator::requireEnum(
                VehicleRegistrationApplicationFilePurposeType::class,
                $data,
                'purposeType',
                self::CONTEXT,
            ),
            mediaType: Hydrator::requireString($data, 'mediaType', self::CONTEXT),
            fileAccessKey: Hydrator::requireString($data, 'fileAccessKey', self::CONTEXT),
            expirationTime: Hydrator::requireString($data, 'expirationTime', self::CONTEXT),
        );
    }
}
