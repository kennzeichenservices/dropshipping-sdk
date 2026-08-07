<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleRegistrationXkfzEventFilePurposeType;
use Dropshipping\Support\Hydrator;

/**
 * Represents a file attached to a vehicle registration XKFZ webhook event.
 *
 * Pass the {@see $fileAccessKey} to
 * {@see \Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint::downloadFileContent()}
 * to fetch the content, before {@see $expirationTime} passes.
 *
 * Treat that key as opaque. Its internal structure is going to change, and nothing
 * in it is meant to be parsed, validated or split by consumers.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationXkfzEventFile
{
    private const CONTEXT = 'VehicleRegistrationXkfzEventFile';

    /**
     * @param VehicleRegistrationXkfzEventFilePurposeType $purposeType    The purpose of the file.
     * @param string                                     $mediaType      MIME type of the file.
     * @param string                                     $fileAccessKey  Opaque key identifying the file content.
     * @param string                                     $expirationTime ISO 8601 datetime until which the file access key is valid.
     */
    public function __construct(
        public VehicleRegistrationXkfzEventFilePurposeType $purposeType,
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
                VehicleRegistrationXkfzEventFilePurposeType::class,
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
