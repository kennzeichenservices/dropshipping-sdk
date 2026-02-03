<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Webhook\WebhookMessage;

/**
 * Middleware that validates the webhook payload signature using HMAC-SHA256.
 *
 * Delegates to {@see WebhookSignatureVerifier} and throws a
 * {@see \Dropshipping\Exceptions\WebhookException} when the signature is invalid.
 */
final class SignatureValidationMiddleware implements WebhookMiddlewareInterface
{
    /**
     * @param WebhookSignatureVerifier $verifier Verifier used for HMAC-SHA256 validation.
     */
    public function __construct(
        private readonly WebhookSignatureVerifier $verifier,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Dropshipping\Exceptions\WebhookException If the signature is invalid.
     */
    public function process(WebhookMessage $message, callable $next): WebhookMessage
    {
        $this->verifier->verify($message->getPayload(), $message->getSignature());

        return $next($message);
    }
}
