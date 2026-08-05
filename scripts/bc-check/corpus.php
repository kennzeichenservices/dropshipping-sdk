<?php

declare(strict_types=1);

/**
 * Payload corpus for the differential test.
 *
 * Shapes are taken from the request/response examples in docs/dropshippingApi_2.3.2.yaml
 * and docs/dropshippingWebhooksApi_3.2.0.yaml, plus deliberate edge cases: absent
 * optionals, explicit nulls, empty lists and non-sequential array keys.
 *
 * Each entry: [label, FQCN, payload]
 */

$address = [
    'firstName' => 'Max', 'lastName' => 'Mustermann', 'gender' => 'MALE',
    'streetName' => 'Musterstr', 'houseNumber' => '1', 'zipCode' => '12345',
    'cityName' => 'Berlin', 'countryCode' => 'DE',
];
$euro = ['usageType' => 'EURO', 'city' => 'B', 'middle' => 'AB', 'end' => '123'];
$order = ['id' => 42, 'externalId' => 'ext-1'];
$cust = [
    'registrationOfficeServiceId' => 7, 'licensePlateType' => 'REGULAR',
    'vehicleType' => 'CAR', 'licensePlateNumberComponents' => $euro,
    'seasonStartMonth' => 4, 'seasonEndMonth' => 10,
];
$costItem = ['number' => 1, 'code' => 'C1', 'name' => 'Gebühr', 'amount' => 1250, 'note' => 'n'];

return [
    // --- shared DTOs -----------------------------------------------------------
    ['address/full', 'Dropshipping\DTO\Address', $address],
    ['address/with-optionals', 'Dropshipping\DTO\Address', $address + [
        'taxNumber' => 'T1', 'companyName' => 'ACME', 'additionalField' => 'c/o', 'phoneNumber' => '+49123',
    ]],
    ['address/explicit-null-optionals', 'Dropshipping\DTO\Address', $address + [
        'taxNumber' => null, 'companyName' => null, 'additionalField' => null, 'phoneNumber' => null,
    ]],
    ['address/female', 'Dropshipping\DTO\Address', ['gender' => 'FEMALE'] + $address],

    ['plateFactory/euro', 'Dropshipping\DTO\LicensePlateNumberComponentsFactory', $euro],
    ['plateFactory/parking', 'Dropshipping\DTO\LicensePlateNumberComponentsFactory', ['usageType' => 'PARKING', 'text' => 'P1']],

    ['reservationCustomization/full', 'Dropshipping\DTO\Requests\LicensePlateReservationCustomization', $cust],
    ['reservationCustomization/no-season', 'Dropshipping\DTO\Requests\LicensePlateReservationCustomization', [
        'registrationOfficeServiceId' => 7, 'licensePlateType' => 'REGULAR',
        'vehicleType' => 'CAR', 'licensePlateNumberComponents' => $euro,
    ]],

    // --- responses -------------------------------------------------------------
    ['orderCreation/full', 'Dropshipping\DTO\Responses\OrderCreationResponse', [
        'id' => 1, 'costNetValue' => '1.23',
        'deliveries' => [['id' => 10, 'items' => [['orderItemIndex' => 0], ['orderItemIndex' => 1]]]],
    ]],
    ['orderCreation/no-deliveries', 'Dropshipping\DTO\Responses\OrderCreationResponse', ['id' => 1]],
    ['orderCreation/empty-deliveries', 'Dropshipping\DTO\Responses\OrderCreationResponse', ['id' => 1, 'deliveries' => []]],
    ['orderCreation/null-cost', 'Dropshipping\DTO\Responses\OrderCreationResponse', ['id' => 1, 'costNetValue' => null]],

    ['delivery/full', 'Dropshipping\DTO\Responses\Delivery', ['id' => 5, 'items' => [['orderItemIndex' => 0]]]],
    ['delivery/no-items', 'Dropshipping\DTO\Responses\Delivery', ['id' => 5]],
    ['deliveryItem', 'Dropshipping\DTO\Responses\DeliveryItem', ['orderItemIndex' => 3]],

    ['availability/two', 'Dropshipping\DTO\Responses\AvailabilityCheckResponse', [
        'availableLicensePlateNumbers' => [
            ['city' => 'B', 'middle' => 'AB', 'end' => '1'],
            ['city' => 'B', 'middle' => 'CD', 'end' => '2'],
        ],
    ]],
    ['availability/absent', 'Dropshipping\DTO\Responses\AvailabilityCheckResponse', []],
    ['availability/empty', 'Dropshipping\DTO\Responses\AvailabilityCheckResponse', ['availableLicensePlateNumbers' => []]],

    ['emissionSticker', 'Dropshipping\DTO\Responses\EmissionStickerOrderResponse', [
        'id' => 9, 'delivery' => ['id' => 4], 'costNetValue' => '9.99',
    ]],
    ['licensePlateReservationResponse', 'Dropshipping\DTO\Responses\LicensePlateReservationResponse', [
        'order' => ['id' => 11, 'costNetValue' => '5.00'],
    ]],
    ['vehicleDeregistrationResponse', 'Dropshipping\DTO\Responses\VehicleDeregistrationResponse', ['order' => ['id' => 77]]],
    ['vehicleRegistrationResponse', 'Dropshipping\DTO\Responses\VehicleRegistrationResponse', [
        'order' => ['id' => 88], 'identityVerificationVendorId' => 3, 'customerInputFormUrl' => 'https://x/y',
    ]],
    ['gksOverview', 'Dropshipping\DTO\Responses\OverviewGksConfiguration', ['id' => 'uuid-1', 'name' => 'Cfg']],
    ['gksOverviews/list', 'Dropshipping\DTO\Responses\GksConfigurationOverviewsResponse', [
        'overviewGksConfigurations' => [['id' => 'u1', 'name' => 'A'], ['id' => 'u2', 'name' => 'B']],
    ]],
    ['gksOverviews/empty', 'Dropshipping\DTO\Responses\GksConfigurationOverviewsResponse', ['overviewGksConfigurations' => []]],

    // --- webhooks --------------------------------------------------------------
    ['webhookOrder/full', 'Dropshipping\DTO\Webhooks\WebhookOrder', $order],
    ['webhookOrder/no-externalId', 'Dropshipping\DTO\Webhooks\WebhookOrder', ['id' => 42]],
    ['webhookDelivery/full', 'Dropshipping\DTO\Webhooks\WebhookDelivery', ['id' => 7, 'trackingCode' => 'TC1']],
    ['webhookDelivery/no-tracking', 'Dropshipping\DTO\Webhooks\WebhookDelivery', ['id' => 7]],

    ['ping', 'Dropshipping\DTO\Webhooks\PingEvent', ['eventTime' => '2026-01-01T10:00:00']],
    ['shipment', 'Dropshipping\DTO\Webhooks\DeliveryShipmentEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'delivery' => ['id' => 7, 'trackingCode' => 'TC1'], 'order' => $order,
    ]],
    ['cancellation', 'Dropshipping\DTO\Webhooks\DeliveryCancellationEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'delivery' => ['id' => 7], 'order' => $order,
    ]],
    ['return', 'Dropshipping\DTO\Webhooks\DeliveryReturnEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'delivery' => ['id' => 7], 'order' => $order,
        'returnReason' => 'DAMAGED', 'reshippingOfferExpirationDate' => '2026-02-01',
    ]],
    ['reservationApproval/full', 'Dropshipping\DTO\Webhooks\LicensePlateReservationApprovalEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'reservationPin' => '1234',
        'customization' => $cust, 'reservationPrice' => '12.00',
    ]],
    ['reservationApproval/no-pin', 'Dropshipping\DTO\Webhooks\LicensePlateReservationApprovalEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order,
        'customization' => $cust, 'reservationPrice' => '12.00',
    ]],
    ['reservationRejection/with-alternatives', 'Dropshipping\DTO\Webhooks\LicensePlateReservationRejectionEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'customization' => $cust,
        'proposedAlternativeLicensePlateNumberComponents' => [
            ['city' => 'B', 'middle' => 'XY', 'end' => '9'],
        ],
    ]],
    ['reservationRejection/no-alternatives', 'Dropshipping\DTO\Webhooks\LicensePlateReservationRejectionEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'customization' => $cust,
    ]],
    ['reservationTimeout', 'Dropshipping\DTO\Webhooks\LicensePlateReservationTimeoutEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'customization' => $cust,
    ]],

    ['xkfzMessage/full', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEventMessage', [
        'type' => 'INFO', 'kind' => 'K', 'code' => 'C', 'text' => 'T', 'additional' => 'A',
    ]],
    ['xkfzMessage/minimal', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEventMessage', ['type' => 'INFO']],
    ['xkfzFile', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEventFile', [
        'purposeType' => 'CERTIFICATE', 'mediaType' => 'application/pdf',
        'fileAccessKey' => 'KEY1', 'expirationTime' => '2026-01-02T10:00:00',
    ]],
    ['costItem/full', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdownItem', $costItem],
    ['costItem/minimal', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdownItem', ['number' => 1, 'amount' => 100]],
    ['officeCosts/full', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationRegistrationOfficeCosts', [
        'items' => [$costItem], 'total' => $costItem, 'note' => 'N',
    ]],
    ['officeCosts/minimal', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationRegistrationOfficeCosts', ['items' => []]],
    ['costBreakdown/full', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdown', [
        'kbaCost' => 500, 'registrationOfficeCosts' => ['items' => [$costItem], 'note' => 'N'],
    ]],
    ['costBreakdown/empty', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdown', []],

    ['xkfzEvent/full', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'ACCEPTED',
        'derivedStatus' => 'IN_PROGRESS', 'applicationId' => 'APP1',
        'files' => [[
            'purposeType' => 'CERTIFICATE', 'mediaType' => 'application/pdf',
            'fileAccessKey' => 'KEY1', 'expirationTime' => '2026-01-02T10:00:00',
        ]],
        'costBreakdown' => ['kbaCost' => 500],
        'messages' => [['type' => 'INFO', 'text' => 'T']],
    ]],
    ['xkfzEvent/minimal', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'ACCEPTED',
        'derivedStatus' => 'IN_PROGRESS',
    ]],

    ['xkfzRegMessage/full', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventMessage', [
        'type' => 'INFO', 'kind' => 'K', 'code' => 'C', 'text' => 'T', 'additional' => 'A',
    ]],
    ['xkfzRegMessage/minimal', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventMessage', ['type' => 'INFO']],
    ['xkfzRegFile', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventFile', [
        'purposeType' => 'VEHICLE_REGISTRATION_APPROVAL_NOTICE', 'mediaType' => 'application/pdf',
        'fileAccessKey' => 'KEY1', 'expirationTime' => '2026-01-02T10:00:00',
    ]],
    ['costItemReg/full', 'Dropshipping\DTO\Webhooks\VehicleRegistrationCostBreakdownItem', $costItem],
    ['costItemReg/minimal', 'Dropshipping\DTO\Webhooks\VehicleRegistrationCostBreakdownItem', ['number' => 1, 'amount' => 100]],
    ['officeCostsReg/full', 'Dropshipping\DTO\Webhooks\VehicleRegistrationRegistrationOfficeCosts', [
        'items' => [$costItem], 'total' => $costItem, 'note' => 'N',
    ]],
    ['officeCostsReg/minimal', 'Dropshipping\DTO\Webhooks\VehicleRegistrationRegistrationOfficeCosts', ['items' => []]],
    ['costBreakdownReg/full', 'Dropshipping\DTO\Webhooks\VehicleRegistrationCostBreakdown', [
        'kbaCost' => 500, 'registrationOfficeCosts' => ['items' => [$costItem], 'note' => 'N'],
    ]],
    ['costBreakdownReg/empty', 'Dropshipping\DTO\Webhooks\VehicleRegistrationCostBreakdown', []],
    ['identityVerificationVendor', 'Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationVendor', ['id' => 1]],
    ['xkfzRegLicensePlate/full', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventLicensePlate', [
        'licensePlateNumberComponents' => $euro, 'licensePlateType' => 'ELECTRIC_SEASON',
        'seasonStartMonth' => 1, 'seasonEndMonth' => 2,
    ]],
    ['xkfzRegLicensePlate/minimal', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventLicensePlate', [
        'licensePlateNumberComponents' => $euro, 'licensePlateType' => 'REGULAR',
    ]],

    ['xkfzEventReg/full', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'ACCEPTED',
        'derivedStatus' => 'IN_PROGRESS', 'applicationId' => 'APP1',
        'files' => [[
            'purposeType' => 'VEHICLE_REGISTRATION_APPROVAL_NOTICE', 'mediaType' => 'application/pdf',
            'fileAccessKey' => 'KEY1', 'expirationTime' => '2026-01-02T10:00:00',
        ]],
        'costBreakdown' => ['kbaCost' => 500],
        'messages' => [['type' => 'INFO', 'text' => 'T']],
    ]],
    ['xkfzEventReg/with-license-plate', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'APPROVED_WITH_DOCUMENTS',
        'derivedStatus' => 'SUCCESS',
        'licensePlate' => [
            'licensePlateNumberComponents' => $euro, 'licensePlateType' => 'ELECTRIC_SEASON',
            'seasonStartMonth' => 1, 'seasonEndMonth' => 2,
        ],
    ]],
    ['xkfzEventReg/minimal', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'ACCEPTED',
        'derivedStatus' => 'IN_PROGRESS',
    ]],

    ['identityVerificationInitialized', 'Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationInitializedEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order,
        'identityVerificationVendor' => ['id' => 1], 'identityVerificationUrl' => 'https://example.test/ident/1',
    ]],
    ['identityVerificationSucceeded', 'Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationSucceededEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'identityVerificationVendor' => ['id' => 1],
    ]],
    ['identityVerificationFailed/with-message', 'Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationFailedEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'identityVerificationVendor' => ['id' => 1],
        'message' => 'M',
    ]],
    ['identityVerificationFailed/no-message', 'Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationFailedEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'identityVerificationVendor' => ['id' => 1],
    ]],
    ['documentSignatureInitialized', 'Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureInitializedEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order,
        'identityVerificationVendor' => ['id' => 1], 'documentSignatureUrl' => 'https://example.test/sign/1',
    ]],
    ['documentSignatureSucceeded', 'Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureSucceededEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'identityVerificationVendor' => ['id' => 1],
    ]],
    ['documentSignatureFailed/with-message', 'Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureFailedEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'identityVerificationVendor' => ['id' => 1],
        'message' => 'M',
    ]],
    ['documentSignatureFailed/no-message', 'Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureFailedEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'identityVerificationVendor' => ['id' => 1],
    ]],

    ['unknownEvent', 'Dropshipping\DTO\Webhooks\UnknownWebhookEvent', [
        'eventType' => 'BRAND_NEW', 'eventTime' => '2026-01-01T10:00:00', 'extra' => 1,
    ]],

    // --- non-sequential keys: the case array_values()/requireArrayList() addresses
    ['orderCreation/non-sequential-deliveries', 'Dropshipping\DTO\Responses\OrderCreationResponse', [
        'id' => 1, 'deliveries' => [3 => ['id' => 10], 7 => ['id' => 11]],
    ]],
    ['availability/non-sequential', 'Dropshipping\DTO\Responses\AvailabilityCheckResponse', [
        'availableLicensePlateNumbers' => [5 => ['city' => 'B', 'middle' => 'AB', 'end' => '1']],
    ]],

    // --- malformed input: behaviour here is INTENDED to change ------------------
    ['MALFORMED address/missing-lastName', 'Dropshipping\DTO\Address', ['firstName' => 'Max', 'gender' => 'MALE']],
    ['MALFORMED address/unknown-gender', 'Dropshipping\DTO\Address', ['gender' => 'DIVERSE'] + $address],
    ['MALFORMED xkfz/unknown-status', 'Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'BRAND_NEW_STATUS',
        'derivedStatus' => 'X',
    ]],
    ['MALFORMED xkfzReg/unknown-status', 'Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEvent', [
        'eventTime' => '2026-01-01T10:00:00', 'order' => $order, 'status' => 'BRAND_NEW_STATUS',
        'derivedStatus' => 'X',
    ]],
    ['MALFORMED delivery/missing-id', 'Dropshipping\DTO\Responses\Delivery', ['items' => []]],
    ['MALFORMED gksOverviews/missing-list', 'Dropshipping\DTO\Responses\GksConfigurationOverviewsResponse', []],
    ['MALFORMED webhookOrder/string-id', 'Dropshipping\DTO\Webhooks\WebhookOrder', ['id' => '42']],
];
