<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Enums;

use Dropshipping\Enums\Gender;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\LicensePlateUsageType;
use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleDeregistrationLicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use Dropshipping\Enums\VehicleRegistrationApplicationFilePurposeType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategyType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Enums\VehicleRegistrationXkfzEventFilePurposeType;
use Dropshipping\Enums\VehicleRegistrationXkfzEventStatus;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class EnumValuesTest extends TestCase
{
    public function test_gender_values(): void
    {
        self::assertSame('FEMALE', Gender::Female->value);
        self::assertSame('MALE', Gender::Male->value);
        self::assertSame('UNSPECIFIED', Gender::Unspecified->value);
        self::assertSame(Gender::Male, Gender::from('MALE'));
        self::assertNull(Gender::tryFrom('INVALID'));
    }

    public function test_vehicle_type_values(): void
    {
        self::assertSame('CAR', VehicleType::Car->value);
        self::assertSame('MOTORCYCLE', VehicleType::Motorcycle->value);
        self::assertSame(VehicleType::Car, VehicleType::from('CAR'));
    }

    public function test_license_plate_type_values(): void
    {
        self::assertSame('REGULAR', LicensePlateType::Regular->value);
        self::assertSame('REGULAR_SEASON', LicensePlateType::RegularSeason->value);
        self::assertSame('ELECTRIC', LicensePlateType::Electric->value);
        self::assertSame('ELECTRIC_SEASON', LicensePlateType::ElectricSeason->value);
        self::assertSame('HISTORICAL', LicensePlateType::Historical->value);
        self::assertSame('HISTORICAL_SEASON', LicensePlateType::HistoricalSeason->value);
        self::assertCount(6, LicensePlateType::cases());
    }

    public function test_license_plate_usage_type_values(): void
    {
        self::assertSame('EURO', LicensePlateUsageType::Euro->value);
        self::assertSame('PARKING', LicensePlateUsageType::Parking->value);
    }

    public function test_product_type_values(): void
    {
        self::assertSame('LICENSE_PLATE', ProductType::LicensePlate->value);
        self::assertSame('VEHICLE_DEREGISTRATION', ProductType::VehicleDeregistration->value);
        self::assertSame('VEHICLE_REGISTRATION', ProductType::VehicleRegistration->value);
        self::assertSame('OTHER', ProductType::Other->value);
        self::assertCount(4, ProductType::cases());
    }

    public function test_webhook_event_type_values(): void
    {
        self::assertSame('PING', WebhookEventType::Ping->value);
        self::assertSame('DELIVERY_SHIPMENT', WebhookEventType::DeliveryShipment->value);
        self::assertSame('DELIVERY_RETURN', WebhookEventType::DeliveryReturn->value);
        self::assertSame('DELIVERY_CANCELLATION', WebhookEventType::DeliveryCancellation->value);
        self::assertSame('LICENSE_PLATE_RESERVATION_APPROVAL', WebhookEventType::LicensePlateReservationApproval->value);
        self::assertSame('LICENSE_PLATE_RESERVATION_REJECTION', WebhookEventType::LicensePlateReservationRejection->value);
        self::assertSame('LICENSE_PLATE_RESERVATION_TIMEOUT', WebhookEventType::LicensePlateReservationTimeout->value);
        self::assertSame('VEHICLE_DEREGISTRATION_XKFZ_EVENT', WebhookEventType::VehicleDeregistrationXkfzEvent->value);
        self::assertSame('VEHICLE_REGISTRATION_XKFZ_EVENT', WebhookEventType::VehicleRegistrationXkfzEvent->value);
        self::assertSame('VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED', WebhookEventType::VehicleRegistrationIdentityVerificationInitialized->value);
        self::assertSame('VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_SUCCEEDED', WebhookEventType::VehicleRegistrationIdentityVerificationSucceeded->value);
        self::assertSame('VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_FAILED', WebhookEventType::VehicleRegistrationIdentityVerificationFailed->value);
        self::assertSame('VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED', WebhookEventType::VehicleRegistrationDocumentSignatureInitialized->value);
        self::assertSame('VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED', WebhookEventType::VehicleRegistrationDocumentSignatureSucceeded->value);
        self::assertSame('VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_FAILED', WebhookEventType::VehicleRegistrationDocumentSignatureFailed->value);
        self::assertSame('UNKNOWN', WebhookEventType::Unknown->value);
        self::assertCount(16, WebhookEventType::cases());
    }

    public function test_vehicle_deregistration_vehicle_type_values(): void
    {
        self::assertSame('CAR', VehicleDeregistrationVehicleType::Car->value);
        self::assertSame('LIGHT_MOTORCYCLE', VehicleDeregistrationVehicleType::LightMotorcycle->value);
        self::assertSame('MOTORCYCLE', VehicleDeregistrationVehicleType::Motorcycle->value);
        self::assertSame('OTHER', VehicleDeregistrationVehicleType::Other->value);
        self::assertSame('TRACTOR', VehicleDeregistrationVehicleType::Tractor->value);
        self::assertSame('TRAILER', VehicleDeregistrationVehicleType::Trailer->value);
        self::assertSame('TRUCK', VehicleDeregistrationVehicleType::Truck->value);
        self::assertCount(7, VehicleDeregistrationVehicleType::cases());
        self::assertSame(VehicleDeregistrationVehicleType::Car, VehicleDeregistrationVehicleType::from('CAR'));
    }

    public function test_vehicle_deregistration_license_plate_type_values(): void
    {
        self::assertSame('REGULAR', VehicleDeregistrationLicensePlateType::Regular->value);
        self::assertSame('REGULAR_SEASON', VehicleDeregistrationLicensePlateType::RegularSeason->value);
        self::assertSame('ELECTRIC', VehicleDeregistrationLicensePlateType::Electric->value);
        self::assertSame('ELECTRIC_SEASON', VehicleDeregistrationLicensePlateType::ElectricSeason->value);
        self::assertSame('HISTORICAL', VehicleDeregistrationLicensePlateType::Historical->value);
        self::assertSame('HISTORICAL_SEASON', VehicleDeregistrationLicensePlateType::HistoricalSeason->value);
        self::assertCount(6, VehicleDeregistrationLicensePlateType::cases());
    }

    public function test_vehicle_registration_vehicle_type_values(): void
    {
        self::assertSame('CAR', VehicleRegistrationVehicleType::Car->value);
        self::assertSame('MOTORCYCLE', VehicleRegistrationVehicleType::Motorcycle->value);
        self::assertSame('TRAILER', VehicleRegistrationVehicleType::Trailer->value);
        self::assertCount(3, VehicleRegistrationVehicleType::cases());
        self::assertSame(VehicleRegistrationVehicleType::Car, VehicleRegistrationVehicleType::from('CAR'));
    }

    public function test_vehicle_registration_license_plate_type_values(): void
    {
        self::assertSame('REGULAR', VehicleRegistrationLicensePlateType::Regular->value);
        self::assertSame('REGULAR_SEASON', VehicleRegistrationLicensePlateType::RegularSeason->value);
        self::assertSame('ELECTRIC', VehicleRegistrationLicensePlateType::Electric->value);
        self::assertSame('ELECTRIC_SEASON', VehicleRegistrationLicensePlateType::ElectricSeason->value);
        self::assertSame('HISTORICAL', VehicleRegistrationLicensePlateType::Historical->value);
        self::assertSame('HISTORICAL_SEASON', VehicleRegistrationLicensePlateType::HistoricalSeason->value);
        self::assertCount(6, VehicleRegistrationLicensePlateType::cases());
    }

    public function test_vehicle_registration_license_plate_number_assignment_strategy_values(): void
    {
        self::assertSame('RANDOM', VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Random->value);
        self::assertSame('RESERVATION', VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Reservation->value);
        self::assertSame('RETAINMENT', VehicleRegistrationLicensePlateNumberAssignmentStrategyType::Retainment->value);
        self::assertCount(3, VehicleRegistrationLicensePlateNumberAssignmentStrategyType::cases());
    }

    public function test_vehicle_registration_service_type_code_values(): void
    {
        self::assertSame('NZ', VehicleRegistrationServiceTypeCode::NZ->value);
        self::assertSame('WZ', VehicleRegistrationServiceTypeCode::WZ->value);
        self::assertSame('UO', VehicleRegistrationServiceTypeCode::UO->value);
        self::assertSame('UI', VehicleRegistrationServiceTypeCode::UI->value);
        self::assertSame('UM', VehicleRegistrationServiceTypeCode::UM->value);
        self::assertSame('WG', VehicleRegistrationServiceTypeCode::WG->value);
        self::assertSame('UG', VehicleRegistrationServiceTypeCode::UG->value);
        self::assertSame('HA', VehicleRegistrationServiceTypeCode::HA->value);
        self::assertCount(8, VehicleRegistrationServiceTypeCode::cases());
    }

    public function test_vehicle_registration_xkfz_event_status_values(): void
    {
        self::assertSame('ACCEPTED', VehicleRegistrationXkfzEventStatus::Accepted->value);
        self::assertSame('APPROVED', VehicleRegistrationXkfzEventStatus::Approved->value);
        self::assertSame('APPROVED_WITH_DOCUMENTS', VehicleRegistrationXkfzEventStatus::ApprovedWithDocuments->value);
        self::assertSame('FAILED', VehicleRegistrationXkfzEventStatus::Failed->value);
        self::assertSame('FORWARDED', VehicleRegistrationXkfzEventStatus::Forwarded->value);
        self::assertSame('PROCESSED', VehicleRegistrationXkfzEventStatus::Processed->value);
        self::assertSame('REJECTED', VehicleRegistrationXkfzEventStatus::Rejected->value);
        self::assertSame('REJECTED_WITH_DOCUMENTS', VehicleRegistrationXkfzEventStatus::RejectedWithDocuments->value);
        self::assertSame('UNKNOWN', VehicleRegistrationXkfzEventStatus::Unknown->value);
        self::assertCount(9, VehicleRegistrationXkfzEventStatus::cases());
    }

    public function test_vehicle_registration_xkfz_event_file_purpose_type_values(): void
    {
        self::assertSame('OTHER', VehicleRegistrationXkfzEventFilePurposeType::Other->value);
        self::assertSame('PROVISIONAL_VEHICLE_REGISTRATION_CERTIFICATE', VehicleRegistrationXkfzEventFilePurposeType::ProvisionalVehicleRegistrationCertificate->value);
        self::assertSame('VEHICLE_REGISTRATION_APPLICATION_POWER_OF_ATTORNEY', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationApplicationPowerOfAttorney->value);
        self::assertSame('VEHICLE_REGISTRATION_APPROVAL_NOTICE', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationApprovalNotice->value);
        self::assertSame('VEHICLE_REGISTRATION_CERTIFICATE_TOKEN', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationCertificateToken->value);
        self::assertSame('VEHICLE_REGISTRATION_CHARGES_NOTICE', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationChargesNotice->value);
        self::assertSame('VEHICLE_REGISTRATION_ELECTRONIC_INSURANCE_CONFIRMATION', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationElectronicInsuranceConfirmation->value);
        self::assertSame('VEHICLE_REGISTRATION_GDPR_CONSENT_DECLARATION', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationGdprConsentDeclaration->value);
        self::assertSame('VEHICLE_REGISTRATION_MOTOR_VEHICLE_TAX_SEPA_DIRECT_DEBIT_MANDATE', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationMotorVehicleTaxSepaDirectDebitMandate->value);
        self::assertSame('VEHICLE_REGISTRATION_REJECTION_NOTICE', VehicleRegistrationXkfzEventFilePurposeType::VehicleRegistrationRejectionNotice->value);
        self::assertCount(10, VehicleRegistrationXkfzEventFilePurposeType::cases());
    }

    public function test_vehicle_registration_application_file_purpose_type_values(): void
    {
        self::assertSame('OTHER', VehicleRegistrationApplicationFilePurposeType::Other->value);
        self::assertSame('VEHICLE_REGISTRATION_APPLICATION_POWER_OF_ATTORNEY', VehicleRegistrationApplicationFilePurposeType::VehicleRegistrationApplicationPowerOfAttorney->value);
        self::assertSame('VEHICLE_REGISTRATION_GDPR_CONSENT_DECLARATION', VehicleRegistrationApplicationFilePurposeType::VehicleRegistrationGdprConsentDeclaration->value);
        self::assertSame('VEHICLE_REGISTRATION_MOTOR_VEHICLE_TAX_SEPA_DIRECT_DEBIT_MANDATE', VehicleRegistrationApplicationFilePurposeType::VehicleRegistrationMotorVehicleTaxSepaDirectDebitMandate->value);
        self::assertCount(4, VehicleRegistrationApplicationFilePurposeType::cases());

        // The signed-application set is a strict subset of the XKFZ one; nothing outside it
        // may leak in, or downloads would be routed to the wrong operation.
        self::assertNull(VehicleRegistrationApplicationFilePurposeType::tryFrom('VEHICLE_REGISTRATION_APPROVAL_NOTICE'));
    }

    public function test_from_invalid_value_throws(): void
    {
        $this->expectException(\ValueError::class);
        Gender::from('INVALID');
    }
}
