<?php

declare(strict_types=1);

namespace Dropshipping\Security;

use Dropshipping\Exceptions\WebhookException;

final class WebhookSignatureVerifier
{
    public function __construct(
        private readonly string $signatureSecret,
    ) {
    }

    public function verify(string $payload, string $signature): void
    {
        $expected = hash_hmac('sha256', $payload, $this->signatureSecret);

        if (!hash_equals($expected, $signature)) {
            throw new WebhookException('Invalid webhook signature');
        }
    }
}
