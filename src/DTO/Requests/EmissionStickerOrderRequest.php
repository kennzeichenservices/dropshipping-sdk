<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;

/**
 * Request DTO for creating an emission sticker order with file uploads.
 *
 * Contains the order details, license plate components, emission data,
 * file paths for uploads, and an optional shipper name.
 */
final readonly class EmissionStickerOrderRequest
{
    /**
     * @param string                           $externalId                  External order identifier.
     * @param string                           $email                       Customer email address.
     * @param Address                          $deliveryAddress             Delivery address for the order.
     * @param Address                          $invoiceAddress              Invoice address for the order.
     * @param EuroLicensePlateNumberComponents $licensePlateNumberComponents License plate number components.
     * @param bool                             $electric                    Whether the vehicle is electric.
     * @param string                           $emissionKeyNumber           Emission key number.
     * @param list<string>                     $filePaths                   File paths for upload attachments.
     * @param string|null                      $shipperName                 Optional shipper name.
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

    /**
     * Convert the order data to an associative array, excluding null values.
     *
     * @return array<string, mixed>
     */
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
