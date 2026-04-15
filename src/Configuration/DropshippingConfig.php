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
    private const DEFAULT_API_VERSION = '2.3.0';

    private string $apiVersion;

    private const DEFAULT_TIMEOUT = 55;

    /**
     * @param string      $host                   API host (without scheme).
     * @param int         $dropshippingClientId    Unique client identifier.
     * @param string      $username                API username for authentication.
     * @param string      $password                API password for authentication.
     * @param string|null $webhookSignatureSecret   Secret used to verify webhook signatures.
     * @param string|null $apiVersion              API version override. Falls back to DROPSHIPPING_API_VERSION env var, then the api-version from composer.json.
     * @param int         $timeout                 HTTP request timeout in seconds.
     */
    public function __construct(
        private string $host,
        private int $dropshippingClientId,
        private string $username,
        private string $password,
        private ?string $webhookSignatureSecret = null,
        ?string $apiVersion = null,
        private int $timeout = self::DEFAULT_TIMEOUT,
    ) {
        $this->apiVersion = $apiVersion ?? (getenv('DROPSHIPPING_API_VERSION') ?: self::resolveDefaultApiVersion());
    }

    private static function resolveDefaultApiVersion(): string
    {
        $composerJson = dirname(__DIR__, 2) . '/composer.json';

        if (is_file($composerJson)) {
            $data = json_decode((string) file_get_contents($composerJson), true);

            if (isset($data['api-version']) && is_string($data['api-version'])) {
                return $data['api-version'];
            }
        }

        return self::DEFAULT_API_VERSION;
    }

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
            $this->apiVersion,
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

    /**
     * Get the HTTP request timeout in seconds.
     *
     * @return int The timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
