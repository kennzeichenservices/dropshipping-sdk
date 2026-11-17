<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Webhooks;

use Dropshipping\DTO\Webhooks\VehicleRegistrationDurablePowerOfAttorneyCompletionEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationDurablePowerOfAttorneyErrorEvent;
use Dropshipping\DTO\Webhooks\WebhookEventFactory;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationDurablePowerOfAttorneyEventsTest extends TestCase
{
    public function test_factory_creates_the_completion_event_from_the_spec_example(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION',
            'durablePowerOfAttorney' => ['id' => '11111111-1111-1111-1111-111111111111'],
            'eventTime' => '2023-10-31T12:34:56',
        ]);

        self::assertInstanceOf(VehicleRegistrationDurablePowerOfAttorneyCompletionEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationDurablePowerOfAttorneyCompletion, $event->getEventType());
        self::assertSame('2023-10-31T12:34:56', $event->getEventTime());
        self::assertSame('11111111-1111-1111-1111-111111111111', $event->durablePowerOfAttorney->id);
    }

    public function test_factory_creates_the_error_event_from_the_spec_example(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_ERROR',
            'durablePowerOfAttorney' => ['id' => '11111111-1111-1111-1111-111111111111'],
            'message' => 'error message',
            'eventTime' => '2023-10-31T12:34:56',
        ]);

        self::assertInstanceOf(VehicleRegistrationDurablePowerOfAttorneyErrorEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationDurablePowerOfAttorneyError, $event->getEventType());
        self::assertSame('11111111-1111-1111-1111-111111111111', $event->durablePowerOfAttorney->id);
        self::assertSame('error message', $event->message);
    }

    /**
     * The events carry no order, unlike every registration event before them.
     * An order sent along anyway is ignored rather than rejected.
     */
    public function test_an_unexpected_order_is_ignored(): void
    {
        $event = VehicleRegistrationDurablePowerOfAttorneyCompletionEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'durablePowerOfAttorney' => ['id' => 'x'],
            'order' => ['id' => 1],
        ]);

        self::assertSame('x', $event->durablePowerOfAttorney->id);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('malformedPayloads')]
    public function test_malformed_payloads_raise_an_sdk_exception(array $payload, string $field): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(sprintf('"%s"', $field));

        WebhookEventFactory::fromArray($payload);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function malformedPayloads(): iterable
    {
        $completion = [
            'eventType' => 'VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION',
            'eventTime' => '2023-10-31T12:34:56',
            'durablePowerOfAttorney' => ['id' => '11111111-1111-1111-1111-111111111111'],
        ];
        $error = ['eventType' => 'VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_ERROR', 'message' => 'm'] + $completion;

        yield 'completion without reference' => [array_diff_key($completion, ['durablePowerOfAttorney' => 0]), 'durablePowerOfAttorney'];
        yield 'completion with reference as string' => [['durablePowerOfAttorney' => 'x'] + $completion, 'durablePowerOfAttorney'];
        yield 'completion without id' => [['durablePowerOfAttorney' => []] + $completion, 'id'];
        yield 'completion with numeric id' => [['durablePowerOfAttorney' => ['id' => 1]] + $completion, 'id'];
        yield 'completion without eventTime' => [array_diff_key($completion, ['eventTime' => 0]), 'eventTime'];
        yield 'error without message' => [array_diff_key($error, ['message' => 0]), 'message'];
        yield 'error with null message' => [['message' => null] + $error, 'message'];
        yield 'error with message as array' => [['message' => ['x']] + $error, 'message'];
        yield 'error without reference' => [array_diff_key($error, ['durablePowerOfAttorney' => 0]), 'durablePowerOfAttorney'];
    }
}
