<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the purpose of an application file attached to a
 * VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED webhook event.
 *
 * These are the documents the customer just signed, not the ones the registration
 * office issues later — that set is {@see VehicleRegistrationXkfzEventFilePurposeType},
 * which is broader and overlaps with this one only in part.
 *
 * Note there is no `UNKNOWN` member, so an unmodelled value makes hydration throw
 * a {@see \Dropshipping\Exceptions\DropshippingException}. That mirrors the two
 * XKFZ file purpose enums. Tolerating unknown event *types* does not help here:
 * that switch only covers the `eventType` field.
 */
enum VehicleRegistrationApplicationFilePurposeType: string
{
    case Other = 'OTHER';
    case VehicleRegistrationApplicationPowerOfAttorney = 'VEHICLE_REGISTRATION_APPLICATION_POWER_OF_ATTORNEY';
    case VehicleRegistrationGdprConsentDeclaration = 'VEHICLE_REGISTRATION_GDPR_CONSENT_DECLARATION';
    case VehicleRegistrationMotorVehicleTaxSepaDirectDebitMandate = 'VEHICLE_REGISTRATION_MOTOR_VEHICLE_TAX_SEPA_DIRECT_DEBIT_MANDATE';
}
