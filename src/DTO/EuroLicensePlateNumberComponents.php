<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\LicensePlateUsageType;

/**
 * Data transfer object representing the components of a Euro-format license plate number.
 */
final readonly class EuroLicensePlateNumberComponents implements LicensePlateNumberComponentsInterface
{
    public LicensePlateUsageType $usageType;

    /**
     * Create a new EuroLicensePlateNumberComponents instance.
     */
    public function __construct(
        public string $city,
        public string $middle,
        public string $end,
    ) {
        $this->usageType = LicensePlateUsageType::Euro;
    }

    /**
     * Convert the license plate number components to an associative array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'usageType' => $this->usageType->value,
            'city' => $this->city,
            'middle' => $this->middle,
            'end' => $this->end,
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
            city: $data['city'],
            middle: $data['middle'],
            end: $data['end'],
        );
    }
}
