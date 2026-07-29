<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

/**
 * Response DTO for a vehicle registration request.
 *
 * Besides the created order ID this response carries the customer input form
 * URL. The customer **must** be redirected to that URL to provide the
 * identification data and signatures required for the registration — without
 * it the registration will not be processed. Registration results themselves
 * are delivered asynchronously via webhook events.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final readonly class VehicleRegistrationResponse
{
    /**
     * @param int    $orderId                      The ID of the created order.
     * @param int    $identityVerificationVendorId The ID of the vendor handling the identity verification.
     * @param string $customerInputFormUrl         URL of the form for the customer to provide input for identification and signatures.
     */
    public function __construct(
        public int $orderId,
        public int $identityVerificationVendorId,
        public string $customerInputFormUrl,
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
            orderId: $data['order']['id'],
            identityVerificationVendorId: $data['identityVerificationVendorId'],
            customerInputFormUrl: $data['customerInputFormUrl'],
        );
    }
}
