<?php

declare(strict_types=1);

namespace Dropshipping\Configuration;

/**
 * Holds the configuration for connecting to the dropshipping API.
 *
 * Stores the API host, client ID, credentials and an optional webhook
 * signature secret used for verifying incoming webhook payloads.
 */
final readonly class DropshippingConfig
{
    private const API_VERSION = '2.1.0';

    /**
     * @param string      $host                   API host (without scheme).
     * @param int         $dropshippingClientId    Unique client identifier.
     * @param string      $username                API username for authentication.
     * @param string      $password                API password for authentication.
     * @param string|null $webhookSignatureSecret   Secret used to verify webhook signatures.
     */
    public function __construct(
        private string $host,
        private int $dropshippingClientId,
        private string $username,
        private string $password,
        private ?string $webhookSignatureSecret = null,
    ) {}

    /**
     * Build the versioned API base URL from host, client ID and API version.
     * 
     * @return string The full API base URL.
     */
    public function getBaseUrl(): string
    {
        $host = preg_replace('#^https?://#', '', $this->host);

        return sprintf(
            'https://%s/dropshipping-api/%d/%s',
            rtrim($host, '/'),
            $this->dropshippingClientId,
            self::API_VERSION,
        );
    }

    /**
     * Get the API username.
     * 
     * @return string The API username.
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Get the API password.
     * 
     * @return string The API password.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get the webhook signature secret, if configured.
     * 
     * @return string|null The webhook signature secret or null if not set.
     */
    public function getWebhookSignatureSecret(): ?string
    {
        return $this->webhookSignatureSecret;
    }
}
