<?php

declare(strict_types=1);

namespace Dropshipping;

use Dropshipping\Async\{QueueWebhookDispatcher, WebhookWorker};
use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\LicensePlateItemCustomization;
use Dropshipping\DTO\OrderItem;
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\DTO\Requests\EmissionStickerOrderRequest;
use Dropshipping\DTO\Requests\GksConfigurationCompany;
use Dropshipping\DTO\Requests\GksConfigurationWriteRequest;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Requests\LicensePlateReservationVehicleHolder;
use Dropshipping\DTO\Requests\OrderCreationRequest;
use Dropshipping\DTO\Requests\ReshippedOrderRequest;
use Dropshipping\DTO\Requests\VehicleDeregistrationRequest;
use Dropshipping\DTO\Requests\VehicleDeregistrationVehicleHolder;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleDeregistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained;
use Dropshipping\DTO\VehicleRegistrationPreviousLicensePlate;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationLicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Serialization\ArrayMapper;
use Dropshipping\Webhook\Middleware\{DeserializationMiddleware, PayloadValidationMiddleware, SignatureValidationMiddleware};
use Dropshipping\Webhook\{WebhookDispatcher, WebhookMessage, WebhookPipeline};

/**
 * Convenience facade providing static factory methods for all SDK request DTOs.
 *
 * Import this single class instead of individual DTO namespaces:
 *
 * ```php
 * use Dropshipping\DS;
 * use Dropshipping\Enums\Gender;
 *
 * $address  = DS::address(firstName: 'Max', ..., gender: Gender::Male);
 * $response = $client->orders->create(DS::order(...));
 * ```
 */
final class DS
{
    private function __construct() {}

    // -------------------------------------------------------------------------
    // Shared
    // -------------------------------------------------------------------------

    /**
     * Create an address DTO.
     */
    public static function address(
        string $firstName,
        string $lastName,
        Gender $gender,
        string $streetName,
        string $houseNumber,
        string $zipCode,
        string $cityName,
        string $countryCode,
    ): Address {
        return new Address(
            firstName: $firstName,
            lastName: $lastName,
            gender: $gender,
            streetName: $streetName,
            houseNumber: $houseNumber,
            zipCode: $zipCode,
            cityName: $cityName,
            countryCode: $countryCode,
        );
    }

    /**
     * Create Euro license plate number components (city / middle / end).
     */
    public static function plate(string $city, string $middle, string $end): EuroLicensePlateNumberComponents
    {
        return new EuroLicensePlateNumberComponents(city: $city, middle: $middle, end: $end);
    }

    // -------------------------------------------------------------------------
    // Orders
    // -------------------------------------------------------------------------

    /**
     * Create an order item with a license plate customization.
     */
    public static function orderItem(
        int $productVariantId,
        string $name,
        string $sku,
        int $quantity,
        EuroLicensePlateNumberComponents $plate,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
    ): OrderItem {
        return new OrderItem(
            productVariantId: $productVariantId,
            name: $name,
            sku: $sku,
            quantity: $quantity,
            customization: new LicensePlateItemCustomization(
                licensePlateNumberComponents: $plate,
                seasonStartMonth: $seasonStartMonth,
                seasonEndMonth: $seasonEndMonth,
            ),
        );
    }

    /**
     * Create a standard order request.
     *
     * @param list<OrderItem> $items
     */
    public static function order(
        string $externalId,
        string $email,
        Address $deliveryAddress,
        Address $invoiceAddress,
        array $items,
        ?string $shipperName = null,
    ): OrderCreationRequest {
        return new OrderCreationRequest(
            externalId: $externalId,
            email: $email,
            deliveryAddress: $deliveryAddress,
            invoiceAddress: $invoiceAddress,
            items: $items,
            shipperName: $shipperName,
        );
    }

    /**
     * Create an emission sticker order request (multipart file upload).
     *
     * @param list<string> $filePaths Absolute paths to the files to upload.
     */
    public static function emissionStickerOrder(
        string $externalId,
        string $email,
        Address $deliveryAddress,
        Address $invoiceAddress,
        EuroLicensePlateNumberComponents $plate,
        bool $electric,
        string $emissionKeyNumber,
        array $filePaths,
        ?string $shipperName = null,
    ): EmissionStickerOrderRequest {
        return new EmissionStickerOrderRequest(
            externalId: $externalId,
            email: $email,
            deliveryAddress: $deliveryAddress,
            invoiceAddress: $invoiceAddress,
            licensePlateNumberComponents: $plate,
            electric: $electric,
            emissionKeyNumber: $emissionKeyNumber,
            filePaths: $filePaths,
            shipperName: $shipperName,
        );
    }

    /**
     * Create a reshipped order request for a returned delivery.
     */
    public static function reshippedOrder(
        string $externalId,
        int $returnedDeliveryId,
        Address $deliveryAddress,
        Address $invoiceAddress,
    ): ReshippedOrderRequest {
        return new ReshippedOrderRequest(
            externalId: $externalId,
            returnedDeliveryId: $returnedDeliveryId,
            deliveryAddress: $deliveryAddress,
            invoiceAddress: $invoiceAddress,
        );
    }

    // -------------------------------------------------------------------------
    // License plate availability
    // -------------------------------------------------------------------------

    /**
     * Create a license plate availability check request.
     */
    public static function availabilityCheck(
        int $registrationOfficeServiceId,
        string $city,
        string $middle,
        string $end,
        LicensePlateType $licensePlateType,
        VehicleType $vehicleType,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
    ): AvailabilityCheckRequest {
        return new AvailabilityCheckRequest(
            registrationOfficeServiceId: $registrationOfficeServiceId,
            city: $city,
            middle: $middle,
            end: $end,
            licensePlateType: $licensePlateType,
            vehicleType: $vehicleType,
            seasonStartMonth: $seasonStartMonth,
            seasonEndMonth: $seasonEndMonth,
        );
    }

    // -------------------------------------------------------------------------
    // License plate reservation
    // -------------------------------------------------------------------------

    /**
     * Create a reservation customization DTO.
     */
    public static function reservationCustomization(
        int $registrationOfficeServiceId,
        LicensePlateType $licensePlateType,
        VehicleType $vehicleType,
        EuroLicensePlateNumberComponents $plate,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
    ): LicensePlateReservationCustomization {
        return new LicensePlateReservationCustomization(
            registrationOfficeServiceId: $registrationOfficeServiceId,
            licensePlateType: $licensePlateType,
            vehicleType: $vehicleType,
            licensePlateNumberComponents: $plate,
            seasonStartMonth: $seasonStartMonth,
            seasonEndMonth: $seasonEndMonth,
        );
    }

    /**
     * Create a reservation vehicle holder DTO.
     */
    public static function reservationVehicleHolder(
        Address $address,
        ?string $birthDate = null,
        ?string $placeOfBirth = null,
    ): LicensePlateReservationVehicleHolder {
        return new LicensePlateReservationVehicleHolder(
            address: $address,
            birthDate: $birthDate,
            placeOfBirth: $placeOfBirth,
        );
    }

    /**
     * Create a license plate reservation request.
     */
    public static function licensePlateReservation(
        string $email,
        LicensePlateReservationCustomization $customization,
        LicensePlateReservationVehicleHolder $vehicleHolder,
        ?string $externalOrderId = null,
    ): LicensePlateReservationRequest {
        return new LicensePlateReservationRequest(
            email: $email,
            customization: $customization,
            vehicleHolder: $vehicleHolder,
            externalOrderId: $externalOrderId,
        );
    }

    // -------------------------------------------------------------------------
    // GKS configurations
    // -------------------------------------------------------------------------

    /**
     * Create a GKS company details DTO.
     */
    public static function gksCompany(
        string $name,
        string $streetName,
        string $houseNumber,
        string $zipCode,
        string $cityName,
        string $countryCode,
    ): GksConfigurationCompany {
        return new GksConfigurationCompany(
            name: $name,
            streetName: $streetName,
            houseNumber: $houseNumber,
            zipCode: $zipCode,
            cityName: $cityName,
            countryCode: $countryCode,
        );
    }

    /**
     * Create a GKS configuration write request (used for create and update).
     */
    public static function gksConfiguration(
        string $name,
        string $kopaKey,
        string $username,
        string $password,
        string $publicKeyCertificate,
        string $privateKey,
        GksConfigurationCompany $company,
    ): GksConfigurationWriteRequest {
        return new GksConfigurationWriteRequest(
            name: $name,
            kopaKey: $kopaKey,
            username: $username,
            password: $password,
            publicKeyCertificate: $publicKeyCertificate,
            privateKey: $privateKey,
            company: $company,
        );
    }

    // -------------------------------------------------------------------------
    // Webhooks
    // -------------------------------------------------------------------------

    /**
     * Build the standard webhook processing pipeline.
     *
     * Wires up signature validation, payload validation, and deserialization
     * middleware in the correct order.
     *
     * @param string $signatureSecret       The webhook signature secret from the SDK config.
     * @param bool   $tolerateUnknownEvents When true, event types this SDK version does not know are
     *                                      delivered as {@see \Dropshipping\DTO\Webhooks\UnknownWebhookEvent}
     *                                      instead of throwing. Enable this to stay resilient while the
     *                                      API rolls out beta events such as vehicle registration.
     */
    public static function webhookPipeline(
        string $signatureSecret,
        bool $tolerateUnknownEvents = false,
    ): WebhookPipeline {
        $serializer = new ArrayMapper();

        return (new WebhookPipeline())
            ->pipe(new SignatureValidationMiddleware(new WebhookSignatureVerifier($signatureSecret)))
            ->pipe(new PayloadValidationMiddleware($serializer))
            ->pipe(new DeserializationMiddleware($serializer, $tolerateUnknownEvents));
    }

    /**
     * Create a webhook dispatcher backed by the given pipeline.
     */
    public static function webhookDispatcher(WebhookPipeline $pipeline): WebhookDispatcher
    {
        return new WebhookDispatcher($pipeline);
    }

    /**
     * Create a WebhookMessage from the current HTTP request.
     *
     * Reads the raw body from php://input and pulls signature, webhook ID,
     * and version from the expected server headers.
     */
    public static function incomingWebhook(): WebhookMessage
    {
        return new WebhookMessage(
            payload: file_get_contents('php://input') ?: '',
            signature: $_SERVER['HTTP_X_SIGNATURE'] ?? '',
            webhookId: (int) ($_SERVER['HTTP_X_WEBHOOK_ID'] ?? 0),
            webhookVersion: $_SERVER['HTTP_X_WEBHOOK_VERSION'] ?? '',
        );
    }

    /**
     * Create a queue-based async webhook dispatcher.
     */
    public static function queueWebhookDispatcher(WebhookQueueInterface $queue): QueueWebhookDispatcher
    {
        return new QueueWebhookDispatcher($queue);
    }

    /**
     * Create a webhook worker that processes messages from a queue.
     */
    public static function webhookWorker(WebhookQueueInterface $queue, WebhookDispatcher $dispatcher): WebhookWorker
    {
        return new WebhookWorker($queue, $dispatcher);
    }

    // -------------------------------------------------------------------------
    // Vehicle deregistrations
    // -------------------------------------------------------------------------

    /**
     * Create a vehicle deregistration customization DTO.
     */
    public static function deregistrationCustomization(
        VehicleDeregistrationVehicleType $vehicleType,
        VehicleDeregistrationLicensePlateType $licensePlateType,
        EuroLicensePlateNumberComponents $plate,
        bool $licensePlateReservationIncluded,
        string $vehicleIdentificationNumber,
        string $vehicleRegistrationCertificateSecurityCode,
        string $vehicleRegistrationDate,
        string $rearLicensePlateSecurityCode,
        ?string $frontLicensePlateSecurityCode = null,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
    ): VehicleDeregistrationCustomization {
        return new VehicleDeregistrationCustomization(
            vehicleType: $vehicleType,
            licensePlateType: $licensePlateType,
            licensePlateNumberComponents: $plate,
            licensePlateReservationIncluded: $licensePlateReservationIncluded,
            vehicleIdentificationNumber: $vehicleIdentificationNumber,
            vehicleRegistrationCertificateSecurityCode: $vehicleRegistrationCertificateSecurityCode,
            vehicleRegistrationDate: $vehicleRegistrationDate,
            rearLicensePlateSecurityCode: $rearLicensePlateSecurityCode,
            frontLicensePlateSecurityCode: $frontLicensePlateSecurityCode,
            seasonStartMonth: $seasonStartMonth,
            seasonEndMonth: $seasonEndMonth,
        );
    }

    /**
     * Create a vehicle deregistration request.
     *
     * The vehicle holder address is wrapped automatically — pass the Address directly.
     */
    public static function vehicleDeregistration(
        string $email,
        VehicleDeregistrationCustomization $customization,
        Address $vehicleHolderAddress,
        ?string $externalOrderId = null,
        ?string $gksConfigurationId = null,
        ?string $contractPartnerKopaKey = null,
    ): VehicleDeregistrationRequest {
        return new VehicleDeregistrationRequest(
            email: $email,
            customization: $customization,
            vehicleHolder: new VehicleDeregistrationVehicleHolder(address: $vehicleHolderAddress),
            externalOrderId: $externalOrderId,
            gksConfigurationId: $gksConfigurationId,
            contractPartnerKopaKey: $contractPartnerKopaKey,
        );
    }

    // -------------------------------------------------------------------------
    // Vehicle registrations (beta)
    // -------------------------------------------------------------------------

    /**
     * Create a license plate number assignment strategy letting the registration
     * office pick an arbitrary available number of the given plate type.
     *
     * @experimental Vehicle registration is a beta feature of the dropshipping API (2.3.2).
     */
    public static function randomLicensePlateNumber(
        VehicleRegistrationLicensePlateType $licensePlateType,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
    ): VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom {
        return new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
            licensePlateType: $licensePlateType,
            seasonStartMonth: $seasonStartMonth,
            seasonEndMonth: $seasonEndMonth,
        );
    }

    /**
     * Create a license plate number assignment strategy using a previously
     * reserved number.
     *
     * @experimental Vehicle registration is a beta feature of the dropshipping API (2.3.2).
     */
    public static function reservedLicensePlateNumber(
        EuroLicensePlateNumberComponents $plate,
        VehicleRegistrationLicensePlateType $licensePlateType,
        string $reservationPin,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
    ): VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation {
        return new VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation(
            licensePlateNumberComponents: $plate,
            licensePlateType: $licensePlateType,
            reservationPin: $reservationPin,
            seasonStartMonth: $seasonStartMonth,
            seasonEndMonth: $seasonEndMonth,
        );
    }

    /**
     * Create a license plate number assignment strategy retaining the number of
     * the previous license plate.
     *
     * @experimental Vehicle registration is a beta feature of the dropshipping API (2.3.2).
     */
    public static function retainedLicensePlateNumber(): VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained
    {
        return new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained();
    }

    /**
     * Create the previous license plate for a vehicle registration.
     *
     * @experimental Vehicle registration is a beta feature of the dropshipping API (2.3.2).
     */
    public static function previousLicensePlate(
        EuroLicensePlateNumberComponents $plate,
        VehicleRegistrationLicensePlateType $licensePlateType,
        ?int $seasonStartMonth = null,
        ?int $seasonEndMonth = null,
        ?string $frontLicensePlateSecurityCode = null,
        ?string $rearLicensePlateSecurityCode = null,
    ): VehicleRegistrationPreviousLicensePlate {
        return new VehicleRegistrationPreviousLicensePlate(
            licensePlateNumberComponents: $plate,
            licensePlateType: $licensePlateType,
            seasonStartMonth: $seasonStartMonth,
            seasonEndMonth: $seasonEndMonth,
            frontLicensePlateSecurityCode: $frontLicensePlateSecurityCode,
            rearLicensePlateSecurityCode: $rearLicensePlateSecurityCode,
        );
    }

    /**
     * Create a vehicle registration customization DTO.
     *
     * @experimental Vehicle registration is a beta feature of the dropshipping API (2.3.2).
     */
    public static function registrationCustomization(
        VehicleRegistrationLicensePlateNumberAssignmentStrategyInterface $licensePlateNumberAssignmentStrategy,
        VehicleRegistrationServiceTypeCode $vehicleRegistrationServiceTypeCode,
        bool $deregistered,
        VehicleRegistrationVehicleType $vehicleType,
        string $electronicInsuranceConfirmationNumber,
        string $vehicleIdentificationNumber,
        string $vehicleTitleSecurityCode,
        string $iban,
        string $bic,
        ?string $vehicleRegistrationCertificateSecurityCode = null,
        ?string $vehicleTitleNumber = null,
        ?VehicleRegistrationPreviousLicensePlate $previousLicensePlate = null,
    ): VehicleRegistrationCustomization {
        return new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: $licensePlateNumberAssignmentStrategy,
            vehicleRegistrationServiceTypeCode: $vehicleRegistrationServiceTypeCode,
            deregistered: $deregistered,
            vehicleType: $vehicleType,
            electronicInsuranceConfirmationNumber: $electronicInsuranceConfirmationNumber,
            vehicleIdentificationNumber: $vehicleIdentificationNumber,
            vehicleTitleSecurityCode: $vehicleTitleSecurityCode,
            iban: $iban,
            bic: $bic,
            vehicleRegistrationCertificateSecurityCode: $vehicleRegistrationCertificateSecurityCode,
            vehicleTitleNumber: $vehicleTitleNumber,
            previousLicensePlate: $previousLicensePlate,
        );
    }

    /**
     * Create a vehicle registration request.
     *
     * The vehicle holder is wrapped automatically — pass the address and birth
     * details directly. The API requires both the place of birth and the birth date.
     *
     * @experimental Vehicle registration is a beta feature of the dropshipping API (2.3.2).
     */
    public static function vehicleRegistration(
        string $email,
        VehicleRegistrationCustomization $customization,
        Address $vehicleHolderAddress,
        string $vehicleHolderPlaceOfBirth,
        string $vehicleHolderBirthDate,
        ?string $vehicleHolderBirthName = null,
        ?string $externalOrderId = null,
        ?string $gksConfigurationId = null,
        ?string $contractPartnerKopaKey = null,
    ): VehicleRegistrationRequest {
        return new VehicleRegistrationRequest(
            email: $email,
            customization: $customization,
            vehicleHolder: new VehicleRegistrationVehicleHolder(
                address: $vehicleHolderAddress,
                placeOfBirth: $vehicleHolderPlaceOfBirth,
                birthDate: $vehicleHolderBirthDate,
                birthName: $vehicleHolderBirthName,
            ),
            externalOrderId: $externalOrderId,
            gksConfigurationId: $gksConfigurationId,
            contractPartnerKopaKey: $contractPartnerKopaKey,
        );
    }
}
