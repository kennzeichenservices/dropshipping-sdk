<?php

declare(strict_types=1);

namespace Dropshipping\Security;

use Dropshipping\Exceptions\WebhookException;

/**
 * Verifies webhook payload signatures using HMAC-SHA256.
 */
final class WebhookSignatureVerifier
{
    /**
     * @param string $signatureSecret The shared secret used for HMAC-SHA256 signature generation.
     */
    public function __construct(
        private readonly string $signatureSecret,
    ) {
    }

    /**
     * Verify that the given payload matches the expected signature.
     *
     * @param string $payload   The raw webhook payload body.
     * @param string $signature The HMAC-SHA256 signature to verify against.
     *
     * @throws WebhookException If the computed signature does not match the provided one.
     */
    public function verify(string $payload, string $signature): void
    {
        $expected = hash_hmac('sha256', $payload, $this->signatureSecret);

        if (!hash_equals($expected, $signature)) {
            throw new WebhookException('Invalid webhook signature');
        }
    }
}
