<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Webhooks;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

final class WebhooksEndpoint
{
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }
}
