<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Webhook\WebhookMessage;

final class SignatureValidationMiddleware implements WebhookMiddlewareInterface
{
    public function __construct(
        private readonly WebhookSignatureVerifier $verifier,
    ) {
    }

    public function process(WebhookMessage $message, callable $next): WebhookMessage
    {
        $this->verifier->verify($message->getPayload(), $message->getSignature());

        return $next($message);
    }
}
