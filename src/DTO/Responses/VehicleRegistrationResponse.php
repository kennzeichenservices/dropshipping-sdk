<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for a vehicle registration request.
 *
 * Carries nothing but the created order ID. Everything the customer has to act
 * on arrives asynchronously as webhook events: the identity check URL with
 * VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED, the signing URL with
 * VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED, and the registration
 * office's verdict with VEHICLE_REGISTRATION_XKFZ_EVENT.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.4.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationResponse
{
    private const CONTEXT = 'VehicleRegistrationResponse';

    /**
     * @param int $orderId The ID of the created order.
     */
    public function __construct(
        public int $orderId,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            orderId: Hydrator::requireInt(
                Hydrator::requireArray($data, 'order', self::CONTEXT),
                'id',
                self::CONTEXT . '.order',
            ),
        );
    }
}
