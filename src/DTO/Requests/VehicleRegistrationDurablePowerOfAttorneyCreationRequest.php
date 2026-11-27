<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurations;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * Request DTO for creating a durable power of attorney (Dauervollmacht).
 * Requires API 2.7.0.
 *
 * A durable power of attorney lets the company authorise vehicle registrations
 * once instead of signing a power of attorney per order. Creating it starts the
 * document generation and the signature by {@see $signer}; the outcome arrives
 * as a VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION or
 * VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_ERROR webhook event.
 *
 * ## Expiration date
 *
 * At most one year from today. Left out, the API sets that maximum itself. The
 * SDK checks the date format only: what "today" is depends on the server's
 * clock and time zone, so the upper bound is left to the API.
 *
 * ## Delivery configurations
 *
 * Where the registration documents go. Left out, the API assumes that both are
 * delivered to the vehicle holder. The spec does not say how this relates to the
 * delivery configurations of a registration ordered under the power of attorney.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneyCreationRequest
{
    /**
     * @param VehicleRegistrationDurablePowerOfAttorneyCompany $company                The company the power of attorney is issued for.
     * @param VehicleRegistrationDurablePowerOfAttorneySigner  $signer                 The person signing for the company.
     * @param string|null                                      $expirationDate         Date the power of attorney expires, YYYY-MM-DD, at most one year from today. Null lets the API set the maximum.
     * @param VehicleRegistrationDeliveryConfigurations|null   $deliveryConfigurations Where the registration documents go. Null has both delivered to the vehicle holder.
     *
     * @throws DropshippingException When the expiration date is not a valid date.
     */
    public function __construct(
        public VehicleRegistrationDurablePowerOfAttorneyCompany $company,
        public VehicleRegistrationDurablePowerOfAttorneySigner $signer,
        public ?string $expirationDate = null,
        public ?VehicleRegistrationDeliveryConfigurations $deliveryConfigurations = null,
    ) {
        Validator::requireNullableIsoDate($expirationDate, 'expirationDate');
    }

    /**
     * Convert the request to an associative array, omitting the expiration date
     * and the delivery configurations when null.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'company' => $this->company->toArray(),
            'signer' => $this->signer->toArray(),
        ];

        if ($this->expirationDate !== null) {
            $data['expirationDate'] = $this->expirationDate;
        }

        if ($this->deliveryConfigurations !== null) {
            $data['deliveryConfigurations'] = $this->deliveryConfigurations->toArray();
        }

        return $data;
    }
}
