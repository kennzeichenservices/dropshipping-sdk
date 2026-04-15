# Example: Webhook Processing

Receives and processes incoming webhook events using the built-in middleware pipeline.

## What it does

Sets up a three-stage middleware pipeline and a handler for `DELIVERY_SHIPMENT` events:

1. **`SignatureValidationMiddleware`** -- verifies the HMAC-SHA256 `X-Signature` header.
2. **`PayloadValidationMiddleware`** -- checks that `eventType`, `eventTime`, and `order` are present.
3. **`DeserializationMiddleware`** -- deserializes the payload into a typed event DTO via `WebhookEventFactory`.

The `WebhookDispatcher` forwards the deserialized event to any registered handler whose `supports()` method returns `true`.

## Adding more handlers

Implement `WebhookHandlerInterface` and call `$dispatcher->registerHandler(new YourHandler())`. Available event types are listed in `WebhookEventType`.

## Key classes

| Class | Purpose |
|-------|---------|
| `WebhookPipeline` | Builds the middleware chain |
| `SignatureValidationMiddleware` | Verifies HMAC-SHA256 signature |
| `PayloadValidationMiddleware` | Validates required payload fields |
| `DeserializationMiddleware` | Deserializes payload to typed DTO |
| `WebhookDispatcher` | Routes events to matching handlers |
| `WebhookMessage` | Wraps the raw payload, signature, and webhook metadata |
| `WebhookHandlerInterface` | Interface to implement for custom handlers |

## Run

```bash
php examples/webhooks.php
```

This script reads from `php://input` and `$_SERVER` headers. Deploy it as a webhook endpoint and point the API's webhook URL to it. Replace the `DropshippingConfig` values with your actual credentials.
