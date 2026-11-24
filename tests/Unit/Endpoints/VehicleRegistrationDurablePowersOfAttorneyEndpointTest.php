<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DS;
use Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint;
use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType;
use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyStatus;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * The three durable power of attorney operations of API 2.7.0 on the vehicle
 * registrations endpoint.
 */
final class VehicleRegistrationDurablePowersOfAttorneyEndpointTest extends TestCase
{
    private const BASE_URL = 'https://api.example.com/dropshippingClients/1';
    private const ID = '11111111-1111-1111-1111-111111111111';

    public function test_create_posts_the_request_and_returns_the_created_record(): void
    {
        $sentBody = null;

        $endpoint = $this->endpointReturning(
            new Response(201, [], json_encode(self::record(), JSON_THROW_ON_ERROR)),
            function (RequestInterface $request) use (&$sentBody): void {
                self::assertSame('POST', $request->getMethod());
                self::assertSame(self::BASE_URL . '/vehicleRegistrations/durablePowersOfAttorney', (string) $request->getUri());
                self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
                self::assertNotSame('', $request->getHeaderLine('Authorization'));
                $sentBody = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);
            },
        );

        $created = $endpoint->createDurablePowerOfAttorney(DS::durablePowerOfAttorney(
            company: DS::durablePowerOfAttorneyCompany(
                name: 'Company X',
                industryCode: 'G01',
                streetName: 'Street X',
                houseNumber: '12A',
                zipCode: '12345',
                cityName: 'City X',
                taxData: DS::durablePowerOfAttorneySepaTaxData('DE0123456789000', 'BYLADEM1001'),
                number: 'HRB 123456',
            ),
            signerFirstName: 'Erika',
            signerLastName: 'Musterfrau',
            signerEmail: 'signer@localhost.test',
            expirationDate: '2027-10-31',
        ));

        self::assertSame([
            'company' => [
                'name' => 'Company X',
                'industryCode' => 'G01',
                'streetName' => 'Street X',
                'houseNumber' => '12A',
                'zipCode' => '12345',
                'cityName' => 'City X',
                'taxData' => ['type' => 'SEPA_DIRECT_DEBIT_MANDATE', 'iban' => 'DE0123456789000', 'bic' => 'BYLADEM1001'],
                'number' => 'HRB 123456',
            ],
            'signer' => ['firstName' => 'Erika', 'lastName' => 'Musterfrau', 'email' => 'signer@localhost.test'],
            'expirationDate' => '2027-10-31',
        ], $sentBody);

        self::assertSame(self::ID, $created->id);
        self::assertSame('G01', $created->company->industryCode);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyStatus::Incomplete, $created->status);
        self::assertSame(VehicleRegistrationDurablePowerOfAttorneyCompanyTaxDataType::SepaDirectDebitMandate, $created->company->taxData->type);
    }

    /**
     * 201 is the documented answer. A 400 has to surface with the API's message,
     * not as a hydration error on the error body.
     */
    public function test_create_reports_a_validation_error_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(400, ['X-Trace-Id' => 'trace-1'], '{"error":"expirationDate must not be after 2027-11-17"}'),
        );

        try {
            $endpoint->createDurablePowerOfAttorney(DS::durablePowerOfAttorney(
                company: DS::durablePowerOfAttorneyCompany('Company X', 'G01', 'Street X', '1', '12345', 'City X', DS::durablePowerOfAttorneyCustomsTaxData('123A4567')),
                signerFirstName: 'Erika',
                signerLastName: 'Musterfrau',
                signerEmail: 'signer@localhost.test',
            ));
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertSame('trace-1', $e->getTraceId());
            self::assertSame('expirationDate must not be after 2027-11-17', $e->getMessage());
        }
    }

    public function test_list_sends_a_get_without_body_and_returns_every_record(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(200, [], json_encode(['durablePowersOfAttorney' => [self::record()]], JSON_THROW_ON_ERROR)),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertSame(self::BASE_URL . '/vehicleRegistrations/durablePowersOfAttorney', (string) $request->getUri());
                self::assertSame('', (string) $request->getBody());
            },
        );

        $list = $endpoint->getDurablePowersOfAttorney();

        self::assertCount(1, $list->durablePowersOfAttorney);
        self::assertSame('HRB 123456', $list->durablePowersOfAttorney[0]->company->number);
    }

    public function test_list_reports_a_403_on_an_older_api_version(): void
    {
        $endpoint = $this->endpointReturning(new Response(403, [], '{"error":"Version not enabled"}'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Version not enabled');

        $endpoint->getDurablePowersOfAttorney();
    }

    public function test_list_reports_a_malformed_body_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{"durablePowersOfAttorney":['));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('malformed JSON');

        $endpoint->getDurablePowersOfAttorney();
    }

    public function test_get_by_id_puts_the_id_into_the_path(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(200, [], json_encode(self::record(), JSON_THROW_ON_ERROR)),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertSame(
                    self::BASE_URL . '/vehicleRegistrations/durablePowersOfAttorney/' . self::ID,
                    (string) $request->getUri(),
                );
            },
        );

        self::assertSame(self::ID, $endpoint->getDurablePowerOfAttorney(self::ID)->id);
    }

    public function test_get_by_id_accepts_an_upper_case_uuid(): void
    {
        $id = 'ABCDEF01-2345-6789-ABCD-EF0123456789';

        $endpoint = $this->endpointReturning(
            new Response(200, [], json_encode(['id' => $id] + self::record(), JSON_THROW_ON_ERROR)),
        );

        self::assertSame($id, $endpoint->getDurablePowerOfAttorney($id)->id);
    }

    public function test_get_by_id_reports_a_404_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(new Response(404, [], '{"error":"Not found"}'));

        try {
            $endpoint->getDurablePowerOfAttorney(self::ID);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
    }

    /**
     * Anything but a UUID is stopped before a request goes out, so it can neither
     * reach the listing (empty ID) nor walk to another route (a path segment).
     */
    #[DataProvider('invalidIds')]
    public function test_get_by_id_rejects_a_non_uuid_without_sending(string $id): void
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->expects($this->never())->method('sendRequest');

        $endpoint = $this->endpoint($mockClient);

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('"id" must be a UUID');

        $endpoint->getDurablePowerOfAttorney($id);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidIds(): iterable
    {
        yield 'empty' => [''];
        yield 'order id' => ['12345'];
        yield 'path traversal' => ['../registrations'];
        yield 'uuid with suffix' => [self::ID . '/x'];
        yield 'uuid with trailing newline' => [self::ID . "\n"];
        yield 'uuid without dashes' => ['11111111111111111111111111111111'];
        yield 'non-hex' => ['gggggggg-1111-1111-1111-111111111111'];
    }

    /**
     * The spec example of a record.
     *
     * @return array<string, mixed>
     */
    private static function record(): array
    {
        return [
            'active' => true,
            'company' => [
                'cityName' => 'City X',
                'houseNumber' => '12A',
                'name' => 'Company X',
                'number' => 'HRB 123456',
                'industryCode' => 'G01',
                'streetName' => 'Street X',
                'zipCode' => '12345',
                'taxData' => ['type' => 'SEPA_DIRECT_DEBIT_MANDATE', 'iban' => 'DE0123456789000', 'bic' => 'BYLADEM1001'],
            ],
            'expirationDate' => '2000-10-31',
            'id' => self::ID,
            'signer' => ['email' => 'signer@localhost.test', 'firstName' => 'Erika', 'lastName' => 'Musterfrau'],
            'status' => 'INCOMPLETE',
        ];
    }

    /**
     * @param (callable(RequestInterface): void)|null $assertRequest
     */
    private function endpointReturning(Response $response, ?callable $assertRequest = null): VehicleRegistrationsEndpoint
    {
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

        return $this->endpoint($mockClient);
    }

    private function endpoint(ClientInterface $client): VehicleRegistrationsEndpoint
    {
        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();

        return new VehicleRegistrationsEndpoint(
            new Psr18HttpClient($client),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            self::BASE_URL,
        );
    }
}
