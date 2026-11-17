<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the type of webhook event, including delivery notifications,
 * license plate reservation status changes, the vehicle registration lifecycle
 * and durable powers of attorney.
 */
enum WebhookEventType: string
{
    case Ping = 'PING';
    case DeliveryShipment = 'DELIVERY_SHIPMENT';
    case DeliveryReturn = 'DELIVERY_RETURN';
    case DeliveryCancellation = 'DELIVERY_CANCELLATION';
    case LicensePlateReservationApproval = 'LICENSE_PLATE_RESERVATION_APPROVAL';
    case LicensePlateReservationRejection = 'LICENSE_PLATE_RESERVATION_REJECTION';
    case LicensePlateReservationTimeout = 'LICENSE_PLATE_RESERVATION_TIMEOUT';
    case VehicleDeregistrationXkfzEvent = 'VEHICLE_DEREGISTRATION_XKFZ_EVENT';
    case VehicleRegistrationXkfzEvent = 'VEHICLE_REGISTRATION_XKFZ_EVENT';
    case VehicleRegistrationIdentityVerificationInitialized = 'VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED';
    case VehicleRegistrationIdentityVerificationSucceeded = 'VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_SUCCEEDED';
    case VehicleRegistrationIdentityVerificationFailed = 'VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_FAILED';
    case VehicleRegistrationDocumentSignatureInitialized = 'VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED';
    case VehicleRegistrationDocumentSignatureSucceeded = 'VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED';
    case VehicleRegistrationDocumentSignatureFailed = 'VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_FAILED';
    case VehicleRegistrationDurablePowerOfAttorneyCompletion = 'VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_COMPLETION';
    case VehicleRegistrationDurablePowerOfAttorneyError = 'VEHICLE_REGISTRATION_DURABLE_POWER_OF_ATTORNEY_ERROR';

    /**
     * Placeholder for event types this SDK version does not know.
     *
     * Never sent by the API — assigned locally by {@see \Dropshipping\DTO\Webhooks\UnknownWebhookEvent}
     * when unknown event types are tolerated.
     */
    case Unknown = 'UNKNOWN';
}
