<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook\Middleware;

use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Webhook\Middleware\SignatureValidationMiddleware;
use Dropshipping\Webhook\WebhookMessage;
use PHPUnit\Framework\TestCase;

final class SignatureValidationMiddlewareTest extends TestCase
{
    public function test_process_calls_verifier_and_passes_through(): void
    {
        $secret = 'test-secret';
        $payload = '{"eventType":"PING"}';
        $signature = hash_hmac('sha256', $payload, $secret);

        $verifier = new WebhookSignatureVerifier($secret);
        $middleware = new SignatureValidationMiddleware($verifier);
        $message = new WebhookMessage($payload, $signature, 1, '3.2.0');

        $nextCalled = false;
        $result = $middleware->process($message, function (WebhookMessage $msg) use (&$nextCalled) {
            $nextCalled = true;
            return $msg;
        });

        self::assertTrue($nextCalled);
        self::assertSame($message, $result);
    }

    public function test_process_throws_when_signature_invalid(): void
    {
        $verifier = new WebhookSignatureVerifier('secret');
        $middleware = new SignatureValidationMiddleware($verifier);
        $message = new WebhookMessage('payload', 'bad-sig', 1, '3.2.0');

        $this->expectException(WebhookException::class);

        $middleware->process($message, fn ($msg) => $msg);
    }
}
