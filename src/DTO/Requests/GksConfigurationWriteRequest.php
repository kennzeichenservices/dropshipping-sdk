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
     * GKS client version the API assumes for configurations created before the field existed.
     *
     * Used as the default so existing calls keep the behaviour they had; pass an explicit
     * version to move a configuration to another one.
     */
    public const DEFAULT_CLIENT_VERSION_NUMBER = '2.0';

    /**
     * @param string                  $name                   Configuration name (1–255 characters).
     * @param string                  $kopaKey                KOPA key for KBA authentication.
     * @param string                  $username               Username for KBA authentication.
     * @param string                  $password               Password for KBA authentication.
     * @param string                  $publicKeyCertificate   PEM-encoded public key certificate.
     * @param string                  $privateKey             PEM-encoded private key.
     * @param GksConfigurationCompany $company                Company details for this configuration.
     * @param string                  $gksClientVersionNumber GKS client version to run this configuration on.
     *                                                        Must be one of the versions reported by
     *                                                        {@see \Dropshipping\Endpoints\GksConfigurations\GksConfigurationsEndpoint::getEnabledClientVersions()}.
     */
    public function __construct(
        public string $name,
        public string $kopaKey,
        public string $username,
        public string $password,
        public string $publicKeyCertificate,
        public string $privateKey,
        public GksConfigurationCompany $company,
        public string $gksClientVersionNumber = self::DEFAULT_CLIENT_VERSION_NUMBER,
    ) {
        Validator::requireStringLength($name, 'name', 1, 255);
        Validator::requireNonEmpty($kopaKey, 'kopaKey');
        Validator::requireNonEmpty($username, 'username');
        Validator::requireNonEmpty($password, 'password');
        Validator::requireNonEmpty($publicKeyCertificate, 'publicKeyCertificate');
        Validator::requireNonEmpty($privateKey, 'privateKey');
        // The spec constrains the version to a non-empty string only; which values exist is
        // decided by the API at runtime, so anything beyond the length check would go stale.
        Validator::requireStringLength($gksClientVersionNumber, 'gksClientVersionNumber', 1, 255);
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
            'gksClientVersionNumber' => $this->gksClientVersionNumber,
        ];
    }
}
