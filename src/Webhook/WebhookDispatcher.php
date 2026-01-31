<?php

declare(strict_types=1);

namespace Dropshipping\Webhook;

use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\Exceptions\WebhookException;

final class WebhookDispatcher
{
    /** @var list<WebhookHandlerInterface> */
    private array $handlers = [];

    private readonly WebhookPipeline $pipeline;

    public function __construct(WebhookPipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function registerHandler(WebhookHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

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
