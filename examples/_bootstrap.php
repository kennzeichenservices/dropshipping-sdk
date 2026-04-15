<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$config = new DropshippingConfig(
    host: 'api.example.com',
    dropshippingClientId: 123,
    username: 'your-username',
    password: 'your-password',
    webhookSignatureSecret: 'your-webhook-secret', // optional
);

$factory = new HttpFactory();

$client = new ApiClient(
    config: $config,
    httpClient: new Client(),
    psrRequestFactory: $factory,
    streamFactory: $factory,
);
