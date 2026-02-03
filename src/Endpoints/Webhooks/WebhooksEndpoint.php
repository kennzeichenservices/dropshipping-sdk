<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Webhooks;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for webhook-related operations.
 *
 * This endpoint is a placeholder for future webhook management functionality.
 */
final class WebhooksEndpoint
{
    /**
     * @param Psr18HttpClient $httpClient   HTTP client for sending requests
     * @param RequestFactory  $requestFactory Factory for creating HTTP request objects
     * @param ResponseMapper  $responseMapper Mapper for processing HTTP responses
     * @param string          $baseUrl        Base URL of the dropshipping API
     */
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }
}
