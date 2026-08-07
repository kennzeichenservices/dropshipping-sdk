<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the purpose of a file attached to a vehicle registration XKFZ event.
 *
 * Unlike the status enums, this one genuinely differs from its deregistration
 * counterpart {@see VehicleDeregistrationXkfzEventFilePurposeType} — registrations
 * produce their own set of documents.
 *
 * Note there is no `UNKNOWN` member, so an unmodelled value makes hydration throw
 * a {@see \Dropshipping\Exceptions\DropshippingException}. That mirrors the
 * deregistration counterpart. Tolerating unknown event *types* does not help here:
 * that switch only covers the `eventType` field.
 */
enum VehicleRegistrationXkfzEventFilePurposeType: string
{
    case Other = 'OTHER';
    case ProvisionalVehicleRegistrationCertificate = 'PROVISIONAL_VEHICLE_REGISTRATION_CERTIFICATE';
    case VehicleRegistrationApplicationPowerOfAttorney = 'VEHICLE_REGISTRATION_APPLICATION_POWER_OF_ATTORNEY';
    case VehicleRegistrationApprovalNotice = 'VEHICLE_REGISTRATION_APPROVAL_NOTICE';
    case VehicleRegistrationCertificateToken = 'VEHICLE_REGISTRATION_CERTIFICATE_TOKEN';
    case VehicleRegistrationChargesNotice = 'VEHICLE_REGISTRATION_CHARGES_NOTICE';
    case VehicleRegistrationElectronicInsuranceConfirmation = 'VEHICLE_REGISTRATION_ELECTRONIC_INSURANCE_CONFIRMATION';
    case VehicleRegistrationGdprConsentDeclaration = 'VEHICLE_REGISTRATION_GDPR_CONSENT_DECLARATION';
    case VehicleRegistrationMotorVehicleTaxSepaDirectDebitMandate = 'VEHICLE_REGISTRATION_MOTOR_VEHICLE_TAX_SEPA_DIRECT_DEBIT_MANDATE';
    case VehicleRegistrationRejectionNotice = 'VEHICLE_REGISTRATION_REJECTION_NOTICE';
}
