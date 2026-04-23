<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Webhooks;

use Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdown;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdownItem;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationRegistrationOfficeCosts;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEvent;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEventFile;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEventMessage;
use Dropshipping\Enums\VehicleDeregistrationXkfzEventFilePurposeType;
use Dropshipping\Enums\VehicleDeregistrationXkfzEventStatus;
use Dropshipping\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class VehicleDeregistrationXkfzEventTest extends TestCase
{
    public function test_fromArray_with_full_payload(): void
    {
        $event = VehicleDeregistrationXkfzEvent::fromArray([
            'eventTime' => '2026-04-12T20:35:07',
            'order' => ['id' => 7, 'externalId' => 'dropshipping-client-external-id'],
            'status' => 'APPROVED_WITH_DOCUMENTS',
            'derivedStatus' => 'SUCCESS',
            'files' => [
                ['purposeType' => 'CERTIFICATE', 'mediaType' => 'application/pdf', 'fileAccessKey' => '7_4d61a48b-7209-4e4c-959b-fedfbda248e2_4', 'expirationTime' => '2026-04-12T21:35:07'],
                ['purposeType' => 'RECEIPT', 'mediaType' => 'image/jpeg', 'fileAccessKey' => '7_4d61a48b-7209-4e4c-959b-fedfbda248e2_5', 'expirationTime' => '2026-04-12T22:35:07'],
            ],
            'costBreakdown' => [
                'kbaCost' => 123,
                'registrationOfficeCosts' => [
                    'items' => [
                        ['number' => 1, 'code' => 'Code1', 'name' => 'Name1', 'amount' => 100, 'note' => 'Note1'],
                        ['number' => 2, 'code' => 'Code2', 'name' => 'Name2', 'amount' => 200, 'note' => 'Note2'],
                    ],
                    'total' => ['number' => 3, 'code' => 'Code3', 'name' => 'Name3', 'amount' => 300, 'note' => 'Note3'],
                    'note' => 'NoteCharges',
                ],
            ],
            'messages' => [
                ['type' => 'Type 1', 'kind' => 'Kind 1', 'code' => 'C1', 'text' => 'Text 1', 'additional' => 'Additional 1'],
                ['type' => 'Type 2', 'kind' => null, 'code' => null, 'text' => null, 'additional' => null],
            ],
        ]);

        self::assertSame(WebhookEventType::VehicleDeregistrationXkfzEvent, $event->getEventType());
        self::assertSame('2026-04-12T20:35:07', $event->getEventTime());
        self::assertSame(7, $event->order->id);
        self::assertSame('dropshipping-client-external-id', $event->order->externalId);

        self::assertSame(VehicleDeregistrationXkfzEventStatus::ApprovedWithDocuments, $event->status);
        self::assertSame('SUCCESS', $event->derivedStatus);

        self::assertNotNull($event->files);
        self::assertCount(2, $event->files);
        self::assertInstanceOf(VehicleDeregistrationXkfzEventFile::class, $event->files[0]);
        self::assertSame(VehicleDeregistrationXkfzEventFilePurposeType::Certificate, $event->files[0]->purposeType);
        self::assertSame('application/pdf', $event->files[0]->mediaType);
        self::assertSame('7_4d61a48b-7209-4e4c-959b-fedfbda248e2_4', $event->files[0]->fileAccessKey);
        self::assertSame('2026-04-12T21:35:07', $event->files[0]->expirationTime);
        self::assertSame(VehicleDeregistrationXkfzEventFilePurposeType::Receipt, $event->files[1]->purposeType);
        self::assertSame('image/jpeg', $event->files[1]->mediaType);
        self::assertSame('2026-04-12T22:35:07', $event->files[1]->expirationTime);

        self::assertNotNull($event->costBreakdown);
        self::assertSame(123, $event->costBreakdown->kbaCost);

        $officeCosts = $event->costBreakdown->registrationOfficeCosts;
        self::assertNotNull($officeCosts);
        self::assertCount(2, $officeCosts->items);
        self::assertSame(1, $officeCosts->items[0]->number);
        self::assertSame('Code1', $officeCosts->items[0]->code);
        self::assertSame('Name1', $officeCosts->items[0]->name);
        self::assertSame(100, $officeCosts->items[0]->amount);
        self::assertSame('Note1', $officeCosts->items[0]->note);
        self::assertSame(300, $officeCosts->total->amount);
        self::assertSame('NoteCharges', $officeCosts->note);

        self::assertNotNull($event->messages);
        self::assertCount(2, $event->messages);
        self::assertInstanceOf(VehicleDeregistrationXkfzEventMessage::class, $event->messages[0]);
        self::assertSame('Type 1', $event->messages[0]->type);
        self::assertSame('Kind 1', $event->messages[0]->kind);
        self::assertSame('C1', $event->messages[0]->code);
        self::assertSame('Text 1', $event->messages[0]->text);
        self::assertSame('Additional 1', $event->messages[0]->additional);
        self::assertSame('Type 2', $event->messages[1]->type);
        self::assertNull($event->messages[1]->kind);
    }

    public function test_fromArray_without_optional_fields(): void
    {
        $event = VehicleDeregistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'PROCESSED',
            'derivedStatus' => 'SUCCESS',
        ]);

        self::assertSame(WebhookEventType::VehicleDeregistrationXkfzEvent, $event->getEventType());
        self::assertSame(42, $event->order->id);
        self::assertNull($event->order->externalId);
        self::assertSame(VehicleDeregistrationXkfzEventStatus::Processed, $event->status);
        self::assertSame('SUCCESS', $event->derivedStatus);
        self::assertNull($event->files);
        self::assertNull($event->costBreakdown);
        self::assertNull($event->messages);
    }

    public function test_fromArray_with_partial_cost_breakdown(): void
    {
        $event = VehicleDeregistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'FAILED',
            'derivedStatus' => 'FAILED',
            'costBreakdown' => [
                'kbaCost' => 500,
            ],
        ]);

        self::assertSame(VehicleDeregistrationXkfzEventStatus::Failed, $event->status);
        self::assertNotNull($event->costBreakdown);
        self::assertSame(500, $event->costBreakdown->kbaCost);
        self::assertNull($event->costBreakdown->registrationOfficeCosts);
    }

    public function test_cost_breakdown_item_fromArray(): void
    {
        $item = VehicleDeregistrationCostBreakdownItem::fromArray([
            'number' => 1,
            'code' => 'ABC',
            'name' => 'Test item',
            'amount' => 100,
            'note' => 'A note',
        ]);

        self::assertSame(1, $item->number);
        self::assertSame('ABC', $item->code);
        self::assertSame('Test item', $item->name);
        self::assertSame(100, $item->amount);
        self::assertSame('A note', $item->note);
    }

    public function test_cost_breakdown_item_fromArray_with_nulls(): void
    {
        $item = VehicleDeregistrationCostBreakdownItem::fromArray([
            'number' => 1,
            'amount' => 100,
        ]);

        self::assertNull($item->code);
        self::assertNull($item->name);
        self::assertNull($item->note);
    }

    public function test_registration_office_costs_fromArray_without_optional_fields(): void
    {
        $costs = VehicleDeregistrationRegistrationOfficeCosts::fromArray([
            'items' => [
                ['number' => 1, 'amount' => 100],
            ],
            'total' => ['number' => 1, 'amount' => 100],
        ]);

        self::assertCount(1, $costs->items);
        self::assertNotNull($costs->total);
        self::assertNull($costs->note);
    }

    public function test_cost_breakdown_fromArray_with_all_nulls(): void
    {
        $breakdown = VehicleDeregistrationCostBreakdown::fromArray([]);

        self::assertNull($breakdown->kbaCost);
        self::assertNull($breakdown->registrationOfficeCosts);
    }
}
