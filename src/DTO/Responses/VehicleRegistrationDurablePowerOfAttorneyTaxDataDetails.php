<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;
use Dropshipping\Support\Hydrator;

/**
 * Tax data of a durable power of attorney as the API returns it.
 *
 * One class for both variants rather than the two request classes: a read
 * should not fail because the API returns a variant this SDK version does not
 * model. Which fields are set follows from {@see $type}; an unmodelled type
 * comes back as {@see VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::Unknown}
 * with every field null.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneyTaxDataDetails
{
    private const CONTEXT = 'VehicleRegistrationDurablePowerOfAttorneyTaxDataDetails';

    /**
     * @param VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType $type                   The variant.
     * @param string|null                                                 $iban                   IBAN of the SEPA direct debit mandate.
     * @param string|null                                                 $bic                    BIC of the SEPA direct debit mandate, where one was given.
     * @param string|null                                                 $customsMajorCustomerId Major customer ID of the customs administration.
     */
    public function __construct(
        public VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType $type,
        public ?string $iban = null,
        public ?string $bic = null,
        public ?string $customsMajorCustomerId = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: Hydrator::requireEnum(
                VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::class,
                $data,
                'type',
                self::CONTEXT,
                VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::Unknown,
            ),
            iban: Hydrator::optionalString($data, 'iban', self::CONTEXT),
            bic: Hydrator::optionalString($data, 'bic', self::CONTEXT),
            customsMajorCustomerId: Hydrator::optionalString($data, 'customsMajorCustomerId', self::CONTEXT),
        );
    }
}
