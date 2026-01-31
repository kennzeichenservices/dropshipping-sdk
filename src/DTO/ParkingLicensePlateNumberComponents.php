<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\LicensePlateUsageType;

final readonly class ParkingLicensePlateNumberComponents implements LicensePlateNumberComponentsInterface
{
    public LicensePlateUsageType $usageType;

    public function __construct(
        public string $text,
    ) {
        $this->usageType = LicensePlateUsageType::Parking;
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'usageType' => $this->usageType->value,
            'text' => $this->text,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
        );
    }
}
