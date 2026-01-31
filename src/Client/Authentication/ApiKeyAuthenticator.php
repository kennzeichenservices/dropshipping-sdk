<?php

declare(strict_types=1);

namespace Dropshipping\Client\Authentication;

use Psr\Http\Message\RequestInterface;

final class ApiKeyAuthenticator
{
    private readonly string $encodedCredentials;

    public function __construct(string $username, string $password)
    {
        $this->encodedCredentials = base64_encode($username . ':' . $password);
    }

    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Basic ' . $this->encodedCredentials);
    }
}
