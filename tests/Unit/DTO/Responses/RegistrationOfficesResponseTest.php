<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\RegistrationOffice;
use Dropshipping\DTO\Responses\RegistrationOfficesResponse;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class RegistrationOfficesResponseTest extends TestCase
{
    public function test_fromArray_hydrates_every_field(): void
    {
        $office = RegistrationOffice::fromArray([
            'id' => 1,
            'regionCode' => 'BO',
            'name' => 'Bochum (Stadt)',
            'district' => 'Stadt Bochum',
            'stateName' => 'Nordrhein-Westfalen',
            'reservationPrice' => '0.00',
        ]);

        self::assertSame(1, $office->id);
        self::assertSame('BO', $office->regionCode);
        self::assertSame('Bochum (Stadt)', $office->name);
        self::assertSame('Stadt Bochum', $office->district);
        self::assertSame('Nordrhein-Westfalen', $office->stateName);
        self::assertSame('0.00', $office->reservationPrice);
    }

    /**
     * Region codes carry umlauts, so the payload has to survive as UTF-8 rather
     * than being normalised on the way in.
     */
    public function test_fromArray_keeps_an_umlaut_region_code_intact(): void
    {
        $office = RegistrationOffice::fromArray($this->boerdePayload());

        self::assertSame('BÖ', $office->regionCode);
        self::assertSame('Bördekreis', $office->name);
    }

    public function test_reservationPrice_stays_a_string(): void
    {
        self::assertSame('123.45', RegistrationOffice::fromArray($this->boerdePayload())->reservationPrice);
    }

    public function test_chargesReservationFee_is_false_for_a_zero_price(): void
    {
        $payload = $this->boerdePayload();

        foreach (['0', '0.00', '0.0000'] as $free) {
            $payload['reservationPrice'] = $free;
            self::assertFalse(
                RegistrationOffice::fromArray($payload)->chargesReservationFee(),
                sprintf('"%s" should count as free', $free),
            );
        }
    }

    public function test_chargesReservationFee_is_true_for_a_positive_price(): void
    {
        self::assertTrue(RegistrationOffice::fromArray($this->boerdePayload())->chargesReservationFee());
    }

    /**
     * A price the API sends in an unexpected shape must not be read as free: an
     * office that silently looks free is the costlier of the two mistakes.
     */
    public function test_chargesReservationFee_treats_an_unparsable_price_as_a_fee(): void
    {
        $payload = $this->boerdePayload();

        foreach (['', '12,34', 'kostenlos'] as $odd) {
            $payload['reservationPrice'] = $odd;
            self::assertTrue(
                RegistrationOffice::fromArray($payload)->chargesReservationFee(),
                sprintf('"%s" should not count as free', $odd),
            );
        }
    }

    public function test_fromArray_rejects_a_missing_required_field(): void
    {
        $payload = $this->boerdePayload();
        unset($payload['district']);

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('district');

        RegistrationOffice::fromArray($payload);
    }

    public function test_fromArray_rejects_a_string_id(): void
    {
        $payload = $this->boerdePayload();
        $payload['id'] = '2';

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('id');

        RegistrationOffice::fromArray($payload);
    }

    public function test_fromArray_rejects_a_numeric_reservation_price(): void
    {
        $payload = $this->boerdePayload();
        $payload['reservationPrice'] = 123.45;

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('reservationPrice');

        RegistrationOffice::fromArray($payload);
    }

    public function test_fromArray_rejects_a_missing_registrationOffices_key(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('registrationOffices');

        RegistrationOfficesResponse::fromArray([]);
    }

    public function test_fromArray_rejects_a_listing_that_is_not_a_list_of_objects(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('registrationOffices');

        RegistrationOfficesResponse::fromArray(['registrationOffices' => [1, 2]]);
    }

    public function test_fromArray_accepts_an_empty_listing(): void
    {
        $response = RegistrationOfficesResponse::fromArray(['registrationOffices' => []]);

        self::assertSame([], $response->registrationOffices);
        self::assertNull($response->findById(1));
        self::assertSame([], $response->findByRegionCode('BO'));
    }

    public function test_findById_returns_the_matching_office(): void
    {
        self::assertSame('Bördekreis', $this->listing()->findById(2)?->name);
    }

    public function test_findById_returns_null_for_an_unknown_id(): void
    {
        self::assertNull($this->listing()->findById(4711));
    }

    /**
     * Several offices can issue the same region code, so the lookup returns all of
     * them instead of hiding the rest behind the first hit.
     */
    public function test_findByRegionCode_returns_every_office_with_that_code(): void
    {
        $offices = $this->listing()->findByRegionCode('BO');

        self::assertCount(2, $offices);
        self::assertSame([1, 3], array_map(static fn (RegistrationOffice $o): int => $o->id, $offices));
    }

    public function test_findByRegionCode_reindexes_the_result(): void
    {
        $offices = $this->listing()->findByRegionCode('BÖ');

        self::assertArrayHasKey(0, $offices);
        self::assertSame(2, $offices[0]->id);
    }

    public function test_findByRegionCode_is_exact(): void
    {
        self::assertSame([], $this->listing()->findByRegionCode('bo'));
        self::assertSame([], $this->listing()->findByRegionCode('B'));
    }

    private function listing(): RegistrationOfficesResponse
    {
        return RegistrationOfficesResponse::fromArray([
            'registrationOffices' => [
                [
                    'id' => 1,
                    'regionCode' => 'BO',
                    'name' => 'Bochum (Stadt)',
                    'district' => 'Stadt Bochum',
                    'stateName' => 'Nordrhein-Westfalen',
                    'reservationPrice' => '0.00',
                ],
                $this->boerdePayload(),
                [
                    'id' => 3,
                    'regionCode' => 'BO',
                    'name' => 'Bochum (Außenstelle)',
                    'district' => 'Stadt Bochum',
                    'stateName' => 'Nordrhein-Westfalen',
                    'reservationPrice' => '12.80',
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function boerdePayload(): array
    {
        return [
            'id' => 2,
            'regionCode' => 'BÖ',
            'name' => 'Bördekreis',
            'district' => 'Landkreis Börde',
            'stateName' => 'Sachsen-Anhalt',
            'reservationPrice' => '123.45',
        ];
    }
}
