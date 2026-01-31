<?php

declare(strict_types=1);

namespace Dropshipping\Configuration;

final readonly class DropshippingConfig
{
    public function __construct(
        private string $host,
        private int $dropshippingClientId,
        private string $username,
        private string $password,
        private ?string $webhookSignatureSecret = null,
    ) {
    }

    public function getBaseUrl(): string
    {
        return sprintf('https://%s/dropshippingClients/%d', $this->host, $this->dropshippingClientId);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getWebhookSignatureSecret(): ?string
    {
        return $this->webhookSignatureSecret;
    }
}
