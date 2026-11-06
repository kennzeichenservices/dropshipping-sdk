<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class VehicleRegistrationsEndpointTest extends TestCase
{
    public function test_createRegistration_sends_post_and_returns_response(): void
    {
        $responseBody = json_encode(['order' => ['id' => 88]]);

        $sentBody = null;

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody, &$sentBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/vehicleRegistrations/registrations', (string) $request->getUri());
                $sentBody = (string) $request->getBody();

                return new Response(200, [], $responseBody);
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();
        $endpoint = new VehicleRegistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );

        $customization = new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            deregistered: true,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $vehicleHolder = new VehicleRegistrationVehicleHolder(
            address: $address,
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
        );
        $request = new VehicleRegistrationRequest('test@example.com', $customization, $vehicleHolder);

        $response = $endpoint->createRegistration($request);

        self::assertSame(88, $response->orderId);

        $decoded = json_decode((string) $sentBody, true);
        self::assertSame('VEHICLE_REGISTRATION', $decoded['customization']['productType']);
    }

    public function test_downloadFileContent_returns_raw_binary_and_escapes_the_key(): void
    {
        $binary = "%PDF-1.4\n\x00\x01binary\xff";

        $endpoint = $this->endpointReturning(
            new Response(200, [], $binary),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith(
                    '/vehicleRegistrations/files/content/key%2Fwith%20chars',
                    (string) $request->getUri(),
                );
            },
        );

        self::assertSame($binary, $endpoint->downloadFileContent('key/with chars'));
    }

    public function test_downloadFileContent_throws_api_exception_with_the_error_message(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(404, ['X-Trace-Id' => 'trace-9'], '{"error":"File access key expired"}'),
        );

        try {
            $endpoint->downloadFileContent('expired-key');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('File access key expired', $e->getMessage());
            self::assertSame(404, $e->getStatusCode());
            self::assertSame('trace-9', $e->getTraceId());
        }
    }

    /**
     * A binary download is the most likely endpoint to be fronted by a proxy that answers
     * with an HTML error page, so it must not leak a raw JsonException to the caller.
     */
    public function test_downloadFileContent_reports_a_non_json_error_body_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(502, [], '<html><title>502 Bad Gateway</title></html>'),
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API request failed');

        $endpoint->downloadFileContent('some-key');
    }

    public function test_downloadApplicationFileContent_hits_the_applicationFiles_path(): void
    {
        $binary = "%PDF-1.4\n\x00\x01binary\xff";

        $endpoint = $this->endpointReturning(
            new Response(200, [], $binary),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith(
                    '/vehicleRegistrations/applicationFiles/content/key%2Fwith%20chars',
                    (string) $request->getUri(),
                );
            },
        );

        self::assertSame($binary, $endpoint->downloadApplicationFileContent('key/with chars'));
    }

    public function test_downloadApplicationFileContent_throws_api_exception_with_the_error_message(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(404, ['X-Trace-Id' => 'trace-11'], '{"error":"File access key expired"}'),
        );

        try {
            $endpoint->downloadApplicationFileContent('expired-key');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('File access key expired', $e->getMessage());
            self::assertSame(404, $e->getStatusCode());
            self::assertSame('trace-11', $e->getTraceId());
        }
    }

    public function test_updateIdentityVerificationDocumentType_sends_a_put_with_the_document_type(): void
    {
        $sentBody = null;

        $endpoint = $this->endpointReturning(
            new Response(202, [], ''),
            function (RequestInterface $request) use (&$sentBody): void {
                self::assertSame('PUT', $request->getMethod());
                self::assertStringEndsWith(
                    '/vehicleRegistrations/88/identityVerifications',
                    (string) $request->getUri(),
                );
                self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
                self::assertNotSame('', $request->getHeaderLine('Authorization'));
                $sentBody = (string) $request->getBody();
            },
        );

        $endpoint->updateIdentityVerificationDocumentType(
            88,
            IdentityVerificationDocumentType::GermanPassportWithProofOfAddress,
        );

        self::assertSame(
            ['identityVerificationDocumentType' => 'GERMAN_PASSPORT_WITH_PROOF_OF_ADDRESS'],
            json_decode((string) $sentBody, true, 512, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * The API answers 202 with an empty body. Accepting only 200 would turn every
     * successful change into an exception.
     */
    public function test_updateIdentityVerificationDocumentType_accepts_the_202_empty_body(): void
    {
        $requestWasSent = false;

        $endpoint = $this->endpointReturning(
            new Response(202, [], ''),
            function (RequestInterface $request) use (&$requestWasSent): void {
                $requestWasSent = true;
            },
        );

        $endpoint->updateIdentityVerificationDocumentType(1, IdentityVerificationDocumentType::GermanId);

        self::assertTrue($requestWasSent);
    }

    /**
     * A 200 is not what this operation is documented to answer. Treating it as
     * success would hide a route that silently did something else.
     */
    public function test_updateIdentityVerificationDocumentType_rejects_an_undocumented_200(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{}'));

        $this->expectException(ApiException::class);

        $endpoint->updateIdentityVerificationDocumentType(1, IdentityVerificationDocumentType::GermanId);
    }

    public function test_updateIdentityVerificationDocumentType_reports_a_409_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(409, ['X-Trace-Id' => 'trace-12'], '{"error":"Document signing already initialized"}'),
        );

        try {
            $endpoint->updateIdentityVerificationDocumentType(88, IdentityVerificationDocumentType::ResidencePermit);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('Document signing already initialized', $e->getMessage());
            self::assertSame(409, $e->getStatusCode());
            self::assertSame('trace-12', $e->getTraceId());
        }
    }

    public function test_updateIdentityVerificationDocumentType_reports_a_404_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(new Response(404, [], '{"error":"Order not found"}'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Order not found');

        $endpoint->updateIdentityVerificationDocumentType(4711, IdentityVerificationDocumentType::GermanId);
    }

    /**
     * An order ID that cannot name a real order is rejected before a request goes
     * out, so a mix-up does not travel into the URL path and hit another route.
     */
    public function test_updateIdentityVerificationDocumentType_rejects_a_non_positive_order_id(): void
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->expects($this->never())->method('sendRequest');

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();
        $endpoint = new VehicleRegistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );

        foreach ([0, -1] as $invalid) {
            try {
                $endpoint->updateIdentityVerificationDocumentType(
                    $invalid,
                    IdentityVerificationDocumentType::GermanId,
                );
                self::fail(sprintf('Expected DropshippingException for order ID %d', $invalid));
            } catch (DropshippingException $e) {
                self::assertStringContainsString('orderId', $e->getMessage());
            }
        }
    }

    /**
     * @param (callable(RequestInterface): void)|null $assertRequest
     */
    private function endpointReturning(
        Response $response,
        ?callable $assertRequest = null,
    ): VehicleRegistrationsEndpoint {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($response, $assertRequest) {
                if ($assertRequest !== null) {
                    $assertRequest($request);
                }

                return $response;
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();

        return new VehicleRegistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }
}
