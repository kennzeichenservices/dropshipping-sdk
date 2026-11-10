<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\VehicleHolderIndustriesResponse;
use Dropshipping\DTO\Responses\VehicleHolderIndustry;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleHolderIndustriesResponseTest extends TestCase
{
    public function test_fromArray_hydrates_every_field(): void
    {
        $industry = VehicleHolderIndustry::fromArray($this->tradePayload());

        self::assertSame('G01', $industry->code);
        self::assertSame('G Handel', $industry->sectionName);
        self::assertSame('Handel mit Kraftfahrzeugen, Kraftwagenteilen und -zubehör', $industry->name);
    }

    /**
     * The names carry umlauts and dashes, so the payload has to survive as UTF-8
     * rather than being normalised on the way in.
     */
    public function test_fromArray_keeps_umlauts_intact(): void
    {
        $industry = VehicleHolderIndustry::fromArray($this->rentalPayload());

        self::assertSame('O Erbringung von sonstigen wirtschaftlichen Dienstleistungen', $industry->sectionName);
        self::assertSame('Vermietung von Kraftfahrzeugen ohne Gestellung eines Fahrers', $industry->name);
    }

    public function test_fromArray_rejects_a_missing_required_field(): void
    {
        foreach (['code', 'sectionName', 'name'] as $field) {
            $payload = $this->tradePayload();
            unset($payload[$field]);

            try {
                VehicleHolderIndustry::fromArray($payload);

                self::fail(sprintf('Expected a missing %s to be rejected', $field));
            } catch (DropshippingException $exception) {
                self::assertStringContainsString($field, $exception->getMessage());
            }
        }
    }

    public function test_fromArray_rejects_a_numeric_code(): void
    {
        $payload = $this->tradePayload();
        $payload['code'] = 1;

        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('code');

        VehicleHolderIndustry::fromArray($payload);
    }

    public function test_fromArray_rejects_a_missing_vehicleHolderIndustries_key(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('vehicleHolderIndustries');

        VehicleHolderIndustriesResponse::fromArray([]);
    }

    public function test_fromArray_rejects_a_listing_that_is_not_a_list_of_objects(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('vehicleHolderIndustries');

        VehicleHolderIndustriesResponse::fromArray(['vehicleHolderIndustries' => ['G01', 'O12']]);
    }

    public function test_fromArray_accepts_an_empty_listing(): void
    {
        $response = VehicleHolderIndustriesResponse::fromArray(['vehicleHolderIndustries' => []]);

        self::assertSame([], $response->vehicleHolderIndustries);
        self::assertSame([], $response->findByCode('G01'));
        self::assertFalse($response->hasCode('G01'));
        self::assertSame([], $response->codes());
        self::assertSame([], $response->groupedBySection());
    }

    /**
     * The spec says a code may belong to several industry names, so the lookup
     * returns all of them instead of hiding the rest behind the first hit.
     */
    public function test_findByCode_returns_every_industry_with_that_code(): void
    {
        $industries = $this->listing()->findByCode('G01');

        self::assertCount(2, $industries);
        self::assertSame(
            ['Handel mit Kraftfahrzeugen, Kraftwagenteilen und -zubehör', 'Instandhaltung und Reparatur von Kraftfahrzeugen'],
            array_map(static fn (VehicleHolderIndustry $i): string => $i->name, $industries),
        );
    }

    public function test_findByCode_reindexes_the_result(): void
    {
        $industries = $this->listing()->findByCode('O12');

        self::assertArrayHasKey(0, $industries);
        self::assertSame('O12', $industries[0]->code);
    }

    public function test_findByCode_is_exact(): void
    {
        self::assertSame([], $this->listing()->findByCode('g01'));
        self::assertSame([], $this->listing()->findByCode('G0'));
        self::assertSame([], $this->listing()->findByCode(' G01'));
    }

    public function test_hasCode_answers_whether_an_industry_code_is_accepted(): void
    {
        self::assertTrue($this->listing()->hasCode('G01'));
        self::assertTrue($this->listing()->hasCode('O12'));
        self::assertFalse($this->listing()->hasCode('Z99'));
    }

    public function test_codes_deduplicates_and_keeps_the_api_order(): void
    {
        self::assertSame(['G01', 'O12'], $this->listing()->codes());
    }

    public function test_groupedBySection_groups_without_losing_an_entry(): void
    {
        $grouped = $this->listing()->groupedBySection();

        self::assertSame(
            ['G Handel', 'O Erbringung von sonstigen wirtschaftlichen Dienstleistungen'],
            array_keys($grouped),
        );
        self::assertCount(2, $grouped['G Handel']);
        self::assertCount(1, $grouped['O Erbringung von sonstigen wirtschaftlichen Dienstleistungen']);
    }

    /**
     * PHP casts a numeric array key to an int, so a digits-only section name
     * must not be lost or merged into a neighbouring group.
     */
    public function test_groupedBySection_survives_a_numeric_section_name(): void
    {
        $response = VehicleHolderIndustriesResponse::fromArray([
            'vehicleHolderIndustries' => [
                ['code' => 'G01', 'sectionName' => '123', 'name' => 'Handel'],
                ['code' => 'G02', 'sectionName' => '123', 'name' => 'Reparatur'],
                $this->rentalPayload(),
            ],
        ]);

        $grouped = $response->groupedBySection();

        self::assertCount(2, $grouped);
        self::assertCount(2, $grouped[123]);
        self::assertSame(
            3,
            array_sum(array_map('count', $grouped)),
            'Grouping must not drop an industry',
        );
    }

    public function test_sectionLetter_reads_the_letter_off_the_section_name(): void
    {
        self::assertSame('G', VehicleHolderIndustry::fromArray($this->tradePayload())->sectionLetter());
        self::assertSame('O', VehicleHolderIndustry::fromArray($this->rentalPayload())->sectionLetter());
    }

    /**
     * The section name is free text as far as the spec goes, so a shape the
     * letter cannot be read from must not produce a stray character.
     */
    public function test_sectionLetter_is_empty_for_a_section_name_without_a_leading_letter(): void
    {
        foreach (['', '1 Handel', ' G Handel', '- Handel'] as $sectionName) {
            $payload = $this->tradePayload();
            $payload['sectionName'] = $sectionName;

            self::assertSame(
                '',
                VehicleHolderIndustry::fromArray($payload)->sectionLetter(),
                sprintf('"%s" should yield no section letter', $sectionName),
            );
        }
    }

    public function test_sectionLetter_handles_a_multibyte_leading_letter(): void
    {
        $payload = $this->tradePayload();
        $payload['sectionName'] = 'Ö Sonstiges';

        self::assertSame('Ö', VehicleHolderIndustry::fromArray($payload)->sectionLetter());
    }

    private function listing(): VehicleHolderIndustriesResponse
    {
        return VehicleHolderIndustriesResponse::fromArray([
            'vehicleHolderIndustries' => [
                $this->tradePayload(),
                [
                    'code' => 'G01',
                    'sectionName' => 'G Handel',
                    'name' => 'Instandhaltung und Reparatur von Kraftfahrzeugen',
                ],
                $this->rentalPayload(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function tradePayload(): array
    {
        return [
            'code' => 'G01',
            'sectionName' => 'G Handel',
            'name' => 'Handel mit Kraftfahrzeugen, Kraftwagenteilen und -zubehör',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rentalPayload(): array
    {
        return [
            'code' => 'O12',
            'sectionName' => 'O Erbringung von sonstigen wirtschaftlichen Dienstleistungen',
            'name' => 'Vermietung von Kraftfahrzeugen ohne Gestellung eines Fahrers',
        ];
    }
}
