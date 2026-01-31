<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\LicensePlateUsageType;

final readonly class EuroLicensePlateNumberComponents implements LicensePlateNumberComponentsInterface
{
    public LicensePlateUsageType $usageType;

    public function __construct(
        public string $city,
        public string $middle,
        public string $end,
    ) {
        $this->usageType = LicensePlateUsageType::Euro;
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'usageType' => $this->usageType->value,
            'city' => $this->city,
            'middle' => $this->middle,
            'end' => $this->end,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'],
            middle: $data['middle'],
            end: $data['end'],
        );
    }
}
