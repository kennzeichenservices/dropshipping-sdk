<?php

declare(strict_types=1);

namespace Dropshipping\Exceptions;

/**
 * Exception thrown during webhook processing (e.g. signature validation, payload parsing).
 */
class WebhookException extends DropshippingException
{
}
