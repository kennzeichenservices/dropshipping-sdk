<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyStatus;
use Dropshipping\Support\Hydrator;

/**
 * A durable power of attorney (Dauervollmacht) as the API returns it, from its
 * creation, the listing or the lookup by ID. Requires API 2.7.0.
 *
 * Two independent things decide whether it can be relied on: {@see $status}
 * says whether the documents were generated and signed, {@see $active} whether
 * it was revoked since. {@see isUsable()} combines both with the expiration
 * date.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorney
{
    private const CONTEXT = 'VehicleRegistrationDurablePowerOfAttorney';

    /**
     * @param string                                                  $id                     UUID of the power of attorney. Webhook events reference it by this ID.
     * @param string                                                  $expirationDate         Date the power of attorney expires, YYYY-MM-DD.
     * @param bool                                                    $active                 False once revoked, true otherwise.
     * @param VehicleRegistrationDurablePowerOfAttorneyStatus         $status                 Processing status. Unmodelled values come back as Unknown.
     * @param VehicleRegistrationDurablePowerOfAttorneyCompanyDetails $company                The company it was issued for.
     * @param VehicleRegistrationDurablePowerOfAttorneySignerDetails  $signer                 The person who signs for the company.
     * @param VehicleRegistrationDeliveryConfigurationsDetails|null   $deliveryConfigurations Where the registration documents go. Null where the API returns none, which means both go to the vehicle holder.
     */
    public function __construct(
        public string $id,
        public string $expirationDate,
        public bool $active,
        public VehicleRegistrationDurablePowerOfAttorneyStatus $status,
        public VehicleRegistrationDurablePowerOfAttorneyCompanyDetails $company,
        public VehicleRegistrationDurablePowerOfAttorneySignerDetails $signer,
        public ?VehicleRegistrationDeliveryConfigurationsDetails $deliveryConfigurations = null,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $deliveryConfigurations = Hydrator::optionalArray($data, 'deliveryConfigurations', self::CONTEXT);

        return new self(
            id: Hydrator::requireString($data, 'id', self::CONTEXT),
            expirationDate: Hydrator::requireString($data, 'expirationDate', self::CONTEXT),
            active: Hydrator::requireBool($data, 'active', self::CONTEXT),
            status: Hydrator::requireEnum(
                VehicleRegistrationDurablePowerOfAttorneyStatus::class,
                $data,
                'status',
                self::CONTEXT,
                VehicleRegistrationDurablePowerOfAttorneyStatus::Unknown,
            ),
            company: VehicleRegistrationDurablePowerOfAttorneyCompanyDetails::fromArray(
                Hydrator::requireArray($data, 'company', self::CONTEXT),
            ),
            signer: VehicleRegistrationDurablePowerOfAttorneySignerDetails::fromArray(
                Hydrator::requireArray($data, 'signer', self::CONTEXT),
            ),
            deliveryConfigurations: $deliveryConfigurations === null
                ? null
                : VehicleRegistrationDeliveryConfigurationsDetails::fromArray($deliveryConfigurations),
        );
    }

    /**
     * Whether the power of attorney is signed, not revoked and not yet expired
     * on the given day.
     *
     * The expiration date is treated as the last valid day. The spec does not
     * say whether it is inclusive, so a power of attorney on its last day is
     * the case to double-check against the API.
     *
     * @param \DateTimeInterface|null $today The day to check against, defaults to today in the default time zone.
     */
    public function isUsable(?\DateTimeInterface $today = null): bool
    {
        if (!$this->active || $this->status !== VehicleRegistrationDurablePowerOfAttorneyStatus::Complete) {
            return false;
        }

        // A date that cannot be read is no proof of validity. Only the leading
        // YYYY-MM-DD counts, so a timestamp the API might send instead still works.
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $this->expirationDate, $match) !== 1) {
            return false;
        }

        $day = ($today ?? new \DateTimeImmutable('today'))->format('Y-m-d');

        // Both sides are YYYY-MM-DD, which sorts as a string the way it sorts as a date.
        return $match[0] >= $day;
    }
}
