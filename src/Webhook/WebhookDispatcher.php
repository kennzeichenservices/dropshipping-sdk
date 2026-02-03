<?php

declare(strict_types=1);

namespace Dropshipping\Webhook;

use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\Exceptions\WebhookException;

/**
 * Dispatches processed webhook events to registered handlers.
 *
 * The message is first run through the {@see WebhookPipeline} for
 * validation and deserialization. All handlers that support the
 * resulting event are then invoked in registration order.
 */
final class WebhookDispatcher
{
    /** @var list<WebhookHandlerInterface> */
    private array $handlers = [];

    private readonly WebhookPipeline $pipeline;

    /**
     * @param WebhookPipeline $pipeline The middleware pipeline to process messages through.
     */
    public function __construct(WebhookPipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * Register a webhook event handler.
     *
     * @param WebhookHandlerInterface $handler The handler to register.
     *
     * @return self Fluent interface.
     */
    public function registerHandler(WebhookHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Process the webhook message through the pipeline and dispatch the event to all supporting handlers.
     *
     * @param WebhookMessage $message The incoming webhook message.
     *
     * @throws WebhookException If no event was deserialized from the message.
     */
    public function dispatch(WebhookMessage $message): void
    {
        $message = $this->pipeline->process($message);
        $event = $message->getEvent();

        if ($event === null) {
            throw new WebhookException('No event was deserialized from webhook message');
        }

        foreach ($this->handlers as $handler) {
            if ($handler->supports($event)) {
                $handler->handle($event);
            }
        }
    }
}
