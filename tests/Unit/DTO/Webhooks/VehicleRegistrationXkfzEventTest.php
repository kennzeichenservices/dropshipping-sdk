<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Webhooks;

use Dropshipping\DTO\Webhooks\VehicleRegistrationCostBreakdown;
use Dropshipping\DTO\Webhooks\VehicleRegistrationCostBreakdownItem;
use Dropshipping\DTO\Webhooks\VehicleRegistrationRegistrationOfficeCosts;
use Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventLicensePlate;
use Dropshipping\Enums\LicensePlateUsageType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationXkfzEventFilePurposeType;
use Dropshipping\Enums\VehicleRegistrationXkfzEventStatus;
use Dropshipping\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationXkfzEventTest extends TestCase
{
    public function test_fromArray_with_full_payload(): void
    {
        $event = VehicleRegistrationXkfzEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2, 'externalId' => 'ext-2'],
            'status' => 'APPROVED_WITH_DOCUMENTS',
            'derivedStatus' => 'SUCCESS',
            'files' => [
                ['purposeType' => 'VEHICLE_REGISTRATION_APPROVAL_NOTICE', 'mediaType' => 'application/pdf', 'fileAccessKey' => 'key-1', 'expirationTime' => '2000-10-31T01:30:44'],
                ['purposeType' => 'PROVISIONAL_VEHICLE_REGISTRATION_CERTIFICATE', 'mediaType' => 'image/jpeg', 'fileAccessKey' => 'key-2', 'expirationTime' => '2000-10-31T01:30:46'],
            ],
            'costBreakdown' => [
                'kbaCost' => 123,
                'registrationOfficeCosts' => [
                    'items' => [
                        ['number' => 1, 'code' => 'C1', 'name' => 'Name 1', 'amount' => 100, 'note' => 'Note 1'],
                        ['number' => 2, 'code' => 'C2', 'name' => 'Name 2', 'amount' => 200, 'note' => 'Note 2'],
                    ],
                    'total' => ['number' => 3, 'code' => 'C3', 'name' => 'Name 3', 'amount' => 300, 'note' => 'Note 3'],
                    'note' => 'Note',
                ],
            ],
            'messages' => [
                ['type' => 'Type 1', 'kind' => 'Kind 1', 'code' => 'C1', 'text' => 'Text 1', 'additional' => 'Additional 1'],
                ['type' => 'Type 2', 'kind' => null, 'code' => null, 'text' => null, 'additional' => null],
            ],
            'applicationId' => '88888020001031000123',
            'licensePlate' => [
                'licensePlateNumberComponents' => ['usageType' => 'EURO', 'city' => 'KÜ', 'middle' => 'PO', 'end' => '321'],
                'licensePlateType' => 'ELECTRIC_SEASON',
                'seasonStartMonth' => 1,
                'seasonEndMonth' => 2,
            ],
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationXkfzEvent, $event->getEventType());
        self::assertSame('2023-10-31T12:34:56', $event->getEventTime());
        self::assertSame(2, $event->order->id);
        self::assertSame('ext-2', $event->order->externalId);

        self::assertSame(VehicleRegistrationXkfzEventStatus::ApprovedWithDocuments, $event->status);
        self::assertSame('SUCCESS', $event->derivedStatus);

        self::assertNotNull($event->files);
        self::assertCount(2, $event->files);
        self::assertSame(VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationApprovalNotice, $event->files[0]->purposeType);
        self::assertSame('application/pdf', $event->files[0]->mediaType);
        self::assertSame('key-1', $event->files[0]->fileAccessKey);
        self::assertSame('2000-10-31T01:30:44', $event->files[0]->expirationTime);
        self::assertSame(VehicleRegistrationXkfzEventFilePurposeType::ProvisionalVehicleRegistrationCertificate, $event->files[1]->purposeType);
        self::assertSame('image/jpeg', $event->files[1]->mediaType);

        self::assertNotNull($event->costBreakdown);
        self::assertSame(123, $event->costBreakdown->kbaCost);

        $officeCosts = $event->costBreakdown->registrationOfficeCosts;
        self::assertNotNull($officeCosts);
        self::assertCount(2, $officeCosts->items);
        self::assertSame(1, $officeCosts->items[0]->number);
        self::assertSame('C1', $officeCosts->items[0]->code);
        self::assertSame('Name 1', $officeCosts->items[0]->name);
        self::assertSame(100, $officeCosts->items[0]->amount);
        self::assertSame('Note 1', $officeCosts->items[0]->note);
        self::assertSame(300, $officeCosts->total->amount);
        self::assertSame('Note', $officeCosts->note);

        self::assertNotNull($event->messages);
        self::assertCount(2, $event->messages);
        self::assertSame('Type 1', $event->messages[0]->type);
        self::assertSame('Kind 1', $event->messages[0]->kind);
        self::assertSame('C1', $event->messages[0]->code);
        self::assertSame('Text 1', $event->messages[0]->text);
        self::assertSame('Additional 1', $event->messages[0]->additional);
        self::assertSame('Type 2', $event->messages[1]->type);
        self::assertNull($event->messages[1]->kind);

        self::assertSame('88888020001031000123', $event->applicationId);

        self::assertNotNull($event->licensePlate);
        self::assertSame(LicensePlateUsageType::Euro, $event->licensePlate->licensePlateNumberComponents->usageType);
        self::assertSame('KÜ', $event->licensePlate->licensePlateNumberComponents->city);
        self::assertSame('PO', $event->licensePlate->licensePlateNumberComponents->middle);
        self::assertSame('321', $event->licensePlate->licensePlateNumberComponents->end);
        self::assertSame(VehicleRegistrationLicensePlateType::ElectricSeason, $event->licensePlate->licensePlateType);
        self::assertSame(1, $event->licensePlate->seasonStartMonth);
        self::assertSame(2, $event->licensePlate->seasonEndMonth);
    }

    public function test_fromArray_without_optional_fields(): void
    {
        $event = VehicleRegistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'PROCESSED',
            'derivedStatus' => 'SUCCESS',
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationXkfzEvent, $event->getEventType());
        self::assertSame(42, $event->order->id);
        self::assertNull($event->order->externalId);
        self::assertSame(VehicleRegistrationXkfzEventStatus::Processed, $event->status);
        self::assertSame('SUCCESS', $event->derivedStatus);
        self::assertNull($event->files);
        self::assertNull($event->costBreakdown);
        self::assertNull($event->messages);
        self::assertNull($event->applicationId);
        self::assertNull($event->licensePlate);
    }

    public function test_fromArray_falls_back_to_unknown_status(): void
    {
        $event = VehicleRegistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'BRAND_NEW_STATUS',
            'derivedStatus' => 'IN_PROGRESS',
        ]);

        self::assertSame(VehicleRegistrationXkfzEventStatus::Unknown, $event->status);
    }

    public function test_fromArray_with_partial_cost_breakdown(): void
    {
        $event = VehicleRegistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'FAILED',
            'derivedStatus' => 'FAILED',
            'costBreakdown' => [
                'kbaCost' => 500,
            ],
        ]);

        self::assertSame(VehicleRegistrationXkfzEventStatus::Failed, $event->status);
        self::assertNotNull($event->costBreakdown);
        self::assertSame(500, $event->costBreakdown->kbaCost);
        self::assertNull($event->costBreakdown->registrationOfficeCosts);
    }

    public function test_license_plate_fromArray_without_season_months(): void
    {
        $plate = VehicleRegistrationXkfzEventLicensePlate::fromArray([
            'licensePlateNumberComponents' => ['usageType' => 'EURO', 'city' => 'B', 'middle' => 'AB', 'end' => '1234'],
            'licensePlateType' => 'REGULAR',
        ]);

        self::assertSame('B', $plate->licensePlateNumberComponents->city);
        self::assertSame(VehicleRegistrationLicensePlateType::Regular, $plate->licensePlateType);
        self::assertNull($plate->seasonStartMonth);
        self::assertNull($plate->seasonEndMonth);
    }

    public function test_cost_breakdown_item_fromArray(): void
    {
        $item = VehicleRegistrationCostBreakdownItem::fromArray([
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
        $item = VehicleRegistrationCostBreakdownItem::fromArray([
            'number' => 1,
            'amount' => 100,
        ]);

        self::assertNull($item->code);
        self::assertNull($item->name);
        self::assertNull($item->note);
    }

    public function test_registration_office_costs_fromArray_without_optional_fields(): void
    {
        $costs = VehicleRegistrationRegistrationOfficeCosts::fromArray([
            'items' => [
                ['number' => 1, 'amount' => 100],
            ],
        ]);

        self::assertCount(1, $costs->items);
        self::assertNull($costs->total);
        self::assertNull($costs->note);
    }

    public function test_cost_breakdown_fromArray_with_all_nulls(): void
    {
        $breakdown = VehicleRegistrationCostBreakdown::fromArray([]);

        self::assertNull($breakdown->kbaCost);
        self::assertNull($breakdown->registrationOfficeCosts);
    }
}
