<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Webhooks;

/**
 * API endpoint for webhook-related operations.
 *
 * This endpoint is a placeholder for future webhook management functionality and
 * currently exposes no operations. Webhook subscriptions are configured in the
 * dropshipping self-service GUI, not over HTTP — see the webhooks API description.
 *
 * To receive and process webhooks, use {@see \Dropshipping\DS::webhookPipeline()}
 * and {@see \Dropshipping\DS::webhookDispatcher()} instead.
 */
final class WebhooksEndpoint
{
}
