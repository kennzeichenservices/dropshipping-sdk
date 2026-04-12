<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Support\Validator;

/**
 * Request DTO for creating or updating a GKS (Großkundenschnittstelle) configuration.
 *
 * Contains the configuration name, authentication credentials for
 * the KBA interface, and the associated company details.
 */
final readonly class GksConfigurationWriteRequest
{
    /**
     * @param string                  $name                  Configuration name (1–255 characters).
     * @param string                  $kopaKey               KOPA key for KBA authentication.
     * @param string                  $username              Username for KBA authentication.
     * @param string                  $password              Password for KBA authentication.
     * @param string                  $publicKeyCertificate  PEM-encoded public key certificate.
     * @param string                  $privateKey            PEM-encoded private key.
     * @param GksConfigurationCompany $company               Company details for this configuration.
     */
    public function __construct(
        public string $name,
        public string $kopaKey,
        public string $username,
        public string $password,
        public string $publicKeyCertificate,
        public string $privateKey,
        public GksConfigurationCompany $company,
    ) {
        Validator::requireStringLength($name, 'name', 1, 255);
        Validator::requireNonEmpty($kopaKey, 'kopaKey');
        Validator::requireNonEmpty($username, 'username');
        Validator::requireNonEmpty($password, 'password');
        Validator::requireNonEmpty($publicKeyCertificate, 'publicKeyCertificate');
        Validator::requireNonEmpty($privateKey, 'privateKey');
    }

    /**
     * Convert the write request to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'kopaKey' => $this->kopaKey,
            'username' => $this->username,
            'password' => $this->password,
            'publicKeyCertificate' => $this->publicKeyCertificate,
            'privateKey' => $this->privateKey,
            'company' => $this->company->toArray(),
        ];
    }
}
