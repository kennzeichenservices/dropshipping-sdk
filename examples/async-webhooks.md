# Example: Async Webhook Processing

Enqueues incoming webhooks for background processing using a queue abstraction.

## What it does

Splits webhook processing into two phases:

**Phase 1 — HTTP controller (fast):** Receives the incoming request and immediately pushes the raw `WebhookMessage` onto a queue. Returns quickly without doing any heavy processing inline.

**Phase 2 — Background worker:** Pops messages from the queue and processes them through the full middleware pipeline (signature validation, payload validation, deserialization) and registered handlers.

## Replacing the in-memory queue

The example uses a simple in-memory array queue. In production, implement `WebhookQueueInterface` backed by your queue system:

```php
class RedisWebhookQueue implements WebhookQueueInterface
{
    public function push(WebhookMessage $message): void
    {
        $this->redis->rpush('webhooks', serialize($message));
    }

    public function pop(): ?WebhookMessage
    {
        $raw = $this->redis->lpop('webhooks');
        return $raw ? unserialize($raw) : null;
    }
}
```

## Key classes

| Class | Purpose |
|-------|---------|
| `QueueWebhookDispatcher` | Enqueues a `WebhookMessage` without processing it |
| `WebhookWorker` | Dequeues and dispatches messages; `run(maxMessages: N)` processes up to N messages |
| `WebhookQueueInterface` | Interface to implement for your queue backend |

## Run

```bash
php examples/async-webhooks.php
```

Replace the `DropshippingConfig` values and swap `InMemoryWebhookQueue` for your production queue implementation before deploying.
