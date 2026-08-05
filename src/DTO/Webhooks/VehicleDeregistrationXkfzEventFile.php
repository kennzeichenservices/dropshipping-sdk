<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleDeregistrationXkfzEventFilePurposeType;
use Dropshipping\Support\Hydrator;

/**
 * Represents a file attached to a vehicle deregistration XKFZ webhook event.
 *
 * The {@see fileAccessKey} can be used to download the file content via the
 * VehicleDeregistrationFileContentDownload endpoint.
 */
final readonly class VehicleDeregistrationXkfzEventFile
{
    private const CONTEXT = 'VehicleDeregistrationXkfzEventFile';

    /**
     * @param VehicleDeregistrationXkfzEventFilePurposeType $purposeType    The purpose of the file.
     * @param string                                         $mediaType      MIME type of the file.
     * @param string                                         $fileAccessKey  Key for downloading the file content.
     * @param string                                         $expirationTime ISO 8601 datetime until which the file access key is valid.
     */
    public function __construct(
        public VehicleDeregistrationXkfzEventFilePurposeType $purposeType,
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
                VehicleDeregistrationXkfzEventFilePurposeType::class,
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
