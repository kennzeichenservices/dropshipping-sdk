<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\LicensePlateUsageType;

/**
 * Data transfer object representing the components of a parking license plate number.
 */
final readonly class ParkingLicensePlateNumberComponents implements LicensePlateNumberComponentsInterface
{
    public LicensePlateUsageType $usageType;

    /**
     * Create a new ParkingLicensePlateNumberComponents instance.
     */
    public function __construct(
        public string $text,
    ) {
        $this->usageType = LicensePlateUsageType::Parking;
    }

    /**
     * Convert the parking license plate number components to an associative array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'usageType' => $this->usageType->value,
            'text' => $this->text,
        ];
    }

    /**
     * Create an instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
        );
    }
}
