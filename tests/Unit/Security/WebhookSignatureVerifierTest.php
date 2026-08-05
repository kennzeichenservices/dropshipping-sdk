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

    public function test_construction_throws_for_empty_secret(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Webhook signature secret must not be empty');

        new WebhookSignatureVerifier('');
    }

    public function test_construction_throws_for_whitespace_only_secret(): void
    {
        $this->expectException(WebhookException::class);

        new WebhookSignatureVerifier("  \t\n ");
    }

    /**
     * Regression: an HMAC keyed with an empty string is computable by anyone, so a
     * missing secret used to make every forged payload verify successfully. Rejecting
     * the secret at construction is what closes that hole.
     */
    public function test_empty_secret_cannot_be_used_to_accept_a_forged_payload(): void
    {
        $forgedPayload = '{"eventType":"PING"}';
        $forgedSignature = hash_hmac('sha256', $forgedPayload, '');

        $this->expectException(WebhookException::class);

        (new WebhookSignatureVerifier(''))->verify($forgedPayload, $forgedSignature);
    }
}
