<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\Responses\VehicleRegistrationResponse;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationAddress;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationLegalPersonRecipient;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient;
use Dropshipping\DTO\VehicleRegistrationDeliveryConfigurations;
use Dropshipping\DTO\VehicleRegistrationDeliveryToOwnerDeliveryConfiguration;
use Dropshipping\DTO\VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained;
use Dropshipping\DTO\VehicleRegistrationPickupByThirdPartyDeliveryConfiguration;
use Dropshipping\DTO\VehicleRegistrationPreviousLicensePlate;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\ApiException;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationsIntegrationTest extends TestCase
{
    private static ?ApiClient $client = null;

    public static function setUpBeforeClass(): void
    {
        $host = getenv('DROPSHIPPING_API_HOST');
        $clientId = getenv('DROPSHIPPING_CLIENT_ID');
        $username = getenv('DROPSHIPPING_USERNAME');
        $password = getenv('DROPSHIPPING_PASSWORD');

        if (!$host || !$clientId || !$username || !$password) {
            static::markTestSkipped('Integration test env vars not set');
        }

        // Run this test by opting in explicitly with the API version to exercise,
        // e.g. DROPSHIPPING_API_VERSION=2.5.0. Every case creates real orders.
        if (!getenv('DROPSHIPPING_API_VERSION')) {
            static::markTestSkipped(
                'Vehicle registration test needs an explicit API version — set e.g. DROPSHIPPING_API_VERSION=2.5.0 to run it',
            );
        }

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_create_registration(): void
    {
        $response = $this->submit(self::registrationRequest(null));

        self::assertGreaterThan(0, $response->orderId);
    }

    /**
     * A Halteränderung with only the fields API 2.5.0 requires for it: no ZB II,
     * no eVB, no bank details.
     */
    public function test_create_holder_change(): void
    {
        $response = $this->submit(self::holderChangeRequest(withInsuranceAndTaxDetails: false));

        self::assertGreaterThan(0, $response->orderId);
    }

    /**
     * 2.5.0 makes the eVB number, IBAN and BIC optional for HA without forbidding
     * them. The SDK passes them on, so this pins down that the API takes them.
     */
    public function test_create_holder_change_with_insurance_and_tax_details(): void
    {
        $response = $this->submit(self::holderChangeRequest(withInsuranceAndTaxDetails: true));

        self::assertGreaterThan(0, $response->orderId);
    }

    /**
     * Route the two registration documents every way the API accepts, for real.
     *
     * The response carries nothing but the order ID, so the assertion is thin on
     * purpose: what this pins down is that the API takes each combination at all.
     *
     * Only combinations the API accepts are listed. The ones it rejects — the
     * undocumented all-or-nothing rule around PICKUP_BY_THIRD_PARTY — no longer
     * reach it: {@see VehicleRegistrationDeliveryConfigurations} refuses to build
     * them, and its unit test covers that. Should the API relax the rule, the
     * guard goes first and a case moves in here.
     */
    #[DataProvider('acceptedDeliveryConfigurationProvider')]
    public function test_create_registration_with_delivery_configurations(
        VehicleRegistrationDeliveryConfigurations $deliveryConfigurations,
    ): void {
        $response = $this->submit(self::registrationRequest($deliveryConfigurations));

        self::assertGreaterThan(0, $response->orderId);
    }

    /**
     * @return iterable<string, array{VehicleRegistrationDeliveryConfigurations}>
     */
    public static function acceptedDeliveryConfigurationProvider(): iterable
    {
        // The default spelled out: passing deliveryConfigurations at all means
        // configuring both documents, so this is what omitting it does anyway.
        yield 'both documents to the vehicle holder' => [
            new VehicleRegistrationDeliveryConfigurations(
                vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
                vehicleTitle: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
            ),
        ];

        // Abweichende Lieferadresse for the Fahrzeugbrief only — the dealership case.
        yield 'ZB I to the holder, ZB II to a deviating delivery address' => [
            new VehicleRegistrationDeliveryConfigurations(
                vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
                vehicleTitle: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration(
                    recipient: self::company(),
                ),
            ),
        ];

        yield 'ZB I to a deviating delivery address, ZB II to the holder' => [
            new VehicleRegistrationDeliveryConfigurations(
                vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration(
                    recipient: self::person(),
                ),
                vehicleTitle: new VehicleRegistrationDeliveryToOwnerDeliveryConfiguration(),
            ),
        ];

        // Unlike pickup, a deviating delivery address may differ per document.
        yield 'both documents to deviating delivery addresses of different recipients' => [
            new VehicleRegistrationDeliveryConfigurations(
                vehicleRegistrationCertificate: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration(
                    recipient: self::person(),
                ),
                vehicleTitle: new VehicleRegistrationDeliveryToThirdPartyDeliveryConfiguration(
                    recipient: self::company(),
                ),
            ),
        ];

        // Abholung durch Dritte: the office keeps both documents at the counter.
        yield 'both documents collected by the same natural person' => [
            new VehicleRegistrationDeliveryConfigurations(
                vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration(
                    recipient: self::person(),
                ),
                vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration(
                    recipient: self::person(),
                ),
            ),
        ];

        yield 'both documents collected by the same legal person' => [
            new VehicleRegistrationDeliveryConfigurations(
                vehicleRegistrationCertificate: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration(
                    recipient: self::company(),
                ),
                vehicleTitle: new VehicleRegistrationPickupByThirdPartyDeliveryConfiguration(
                    recipient: self::company(),
                ),
            ),
        ];
    }

    /**
     * Submit a registration, skipping instead of failing when the API version is
     * not enabled for this client.
     */
    private function submit(VehicleRegistrationRequest $request): VehicleRegistrationResponse
    {
        try {
            return self::$client->vehicleRegistrations->createRegistration($request);
        } catch (ApiException $e) {
            if (in_array($e->getStatusCode(), [403, 404], true)) {
                self::markTestSkipped(sprintf(
                    'API version %s is not enabled for this client yet (HTTP %d)',
                    (string) getenv('DROPSHIPPING_API_VERSION'),
                    $e->getStatusCode(),
                ));
            }

            throw $e;
        }
    }

    /**
     * A Neuzulassung of a car, differing between cases only in where its two
     * documents go. Null leaves deliveryConfigurations out entirely.
     */
    private static function registrationRequest(
        ?VehicleRegistrationDeliveryConfigurations $deliveryConfigurations,
    ): VehicleRegistrationRequest {
        $customization = new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            deregistered: true,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'EVB1234',
            vehicleIdentificationNumber: 'W0L000051T2123456',
            // The API checks the format of this code beyond its length, without
            // publishing the rule. This value is the one from the spec example.
            vehicleTitleSecurityCode: 'B12345678900',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            deliveryConfigurations: $deliveryConfigurations,
        );

        return new VehicleRegistrationRequest(
            email: 'dropshipping-api-end-customer@localhost.test',
            customization: $customization,
            vehicleHolder: new VehicleRegistrationVehicleHolder(
                address: self::holderAddress(),
                placeOfBirth: 'Icity',
                birthDate: '1990-01-31',
            ),
            externalOrderId: 'dropshipping-api-registration-' . uniqid(),
        );
    }

    /**
     * A Halteränderung of a registered car that keeps its plate.
     */
    private static function holderChangeRequest(bool $withInsuranceAndTaxDetails): VehicleRegistrationRequest
    {
        $customization = new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::HA,
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: $withInsuranceAndTaxDetails ? 'EVB1234' : null,
            vehicleIdentificationNumber: 'W0L000051T2123456',
            vehicleTitleSecurityCode: null,
            iban: $withInsuranceAndTaxDetails ? 'DE89370400440532013000' : null,
            bic: $withInsuranceAndTaxDetails ? 'COBADEFFXXX' : null,
            vehicleRegistrationCertificateSecurityCode: 'VRCS123',
            previousLicensePlate: new VehicleRegistrationPreviousLicensePlate(
                licensePlateNumberComponents: new EuroLicensePlateNumberComponents('BO', 'EF', '456'),
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
        );

        return new VehicleRegistrationRequest(
            email: 'dropshipping-api-end-customer@localhost.test',
            customization: $customization,
            vehicleHolder: new VehicleRegistrationVehicleHolder(
                address: self::holderAddress(),
                placeOfBirth: 'Icity',
                birthDate: '1990-01-31',
            ),
            externalOrderId: 'dropshipping-api-holder-change-' . uniqid(),
        );
    }

    private static function holderAddress(): Address
    {
        return new Address(
            firstName: 'Ifirst',
            lastName: 'Ilast',
            gender: Gender::Female,
            streetName: 'Istreet',
            houseNumber: '1I',
            zipCode: '22222',
            cityName: 'Icity',
            countryCode: 'DE',
        );
    }

    private static function person(): VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient
    {
        return new VehicleRegistrationDeliveryConfigurationNaturalPersonRecipient(
            address: self::deliveryAddress(),
            firstName: 'Ithirdfirst',
            lastName: 'Ithirdlast',
            gender: Gender::Male,
            birthDate: '1985-07-14',
        );
    }

    private static function company(): VehicleRegistrationDeliveryConfigurationLegalPersonRecipient
    {
        return new VehicleRegistrationDeliveryConfigurationLegalPersonRecipient(
            address: self::deliveryAddress(),
            name: 'Ithirdparty GmbH',
        );
    }

    private static function deliveryAddress(): VehicleRegistrationDeliveryConfigurationAddress
    {
        // Street, house number, postal code and city only — the recipient carries
        // the name, and the office delivers within Germany.
        return new VehicleRegistrationDeliveryConfigurationAddress(
            streetName: 'Ithirdstreet',
            houseNumber: '7a',
            zipCode: '33333',
            cityName: 'Ithirdcity',
        );
    }
}
