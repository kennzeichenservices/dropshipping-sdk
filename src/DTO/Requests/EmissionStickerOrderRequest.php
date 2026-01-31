<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;

final readonly class EmissionStickerOrderRequest
{
    /**
     * @param list<string> $filePaths
     */
    public function __construct(
        public string $externalId,
        public string $email,
        public Address $deliveryAddress,
        public Address $invoiceAddress,
        public EuroLicensePlateNumberComponents $licensePlateNumberComponents,
        public bool $electric,
        public string $emissionKeyNumber,
        public array $filePaths,
        public ?string $shipperName = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toOrderArray(): array
    {
        return array_filter([
            'shipperName' => $this->shipperName,
            'externalId' => $this->externalId,
            'email' => $this->email,
            'deliveryAddress' => $this->deliveryAddress->toArray(),
            'invoiceAddress' => $this->invoiceAddress->toArray(),
            'emissionSticker' => [
                'licensePlateNumberComponents' => $this->licensePlateNumberComponents->toArray(),
                'electric' => $this->electric,
                'emissionKeyNumber' => $this->emissionKeyNumber,
            ],
        ], static fn (mixed $value): bool => $value !== null);
    }
}
