<?php

declare(strict_types=1);

namespace Dropshipping\Client\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 * Handles HTTP Basic authentication for dropshipping API requests.
 *
 * Encodes the given credentials as a Base64 string on construction and
 * applies the Authorization header to outgoing PSR-7 requests.
 */
final class ApiKeyAuthenticator
{
    private readonly string $encodedCredentials;

    /**
     * @param string $username API username.
     * @param string $password API password.
     */
    public function __construct(string $username, string $password)
    {
        $this->encodedCredentials = base64_encode($username . ':' . $password);
    }

    /**
     * Add the HTTP Basic Authorization header to the given request.
     */
    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Basic ' . $this->encodedCredentials);
    }
}
