<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Security;

use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Security\WebhookSignatureVerifier;
use PHPUnit\Framework\TestCase;

final class WebhookSignatureVerifierTest extends TestCase
{
    public function test_verify_passes_for_valid_signature(): void
    {
        $secret = 'test-secret';
        $payload = '{"eventType":"PING"}';
        $signature = hash_hmac('sha256', $payload, $secret);

        $verifier = new WebhookSignatureVerifier($secret);
        $verifier->verify($payload, $signature);

        $this->addToAssertionCount(1);
    }

    public function test_verify_throws_for_invalid_signature(): void
    {
        $verifier = new WebhookSignatureVerifier('secret');

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $verifier->verify('payload', 'wrong-signature');
    }

    public function test_verify_throws_for_empty_signature(): void
    {
        $verifier = new WebhookSignatureVerifier('secret');

        $this->expectException(WebhookException::class);

        $verifier->verify('payload', '');
    }
}
