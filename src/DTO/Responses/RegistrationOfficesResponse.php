<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for the registration office listing.
 *
 * Carries every {@see RegistrationOffice} the client may order through, with the
 * reservation fee each one charges.
 */
final readonly class RegistrationOfficesResponse
{
    private const CONTEXT = 'RegistrationOfficesResponse';

    /**
     * @param list<RegistrationOffice> $registrationOffices The offices.
     */
    public function __construct(
        public array $registrationOffices,
    ) {
    }

    /**
     * Create an instance from a raw API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            registrationOffices: array_map(
                static fn (array $item): RegistrationOffice => RegistrationOffice::fromArray($item),
                Hydrator::requireArrayList($data, 'registrationOffices', self::CONTEXT),
            ),
        );
    }

    /**
     * Find an office by the `id` the listing publishes, for looking up its
     * reservation fee or region code.
     */
    public function findById(int $id): ?RegistrationOffice
    {
        foreach ($this->registrationOffices as $office) {
            if ($office->id === $id) {
                return $office;
            }
        }

        return null;
    }

    /**
     * All offices issuing one region code.
     *
     * A region code is not unique: several offices can serve the same one, so
     * this returns a list rather than a single office. The comparison is exact,
     * so pass the code as the API publishes it, umlauts included.
     *
     * @return list<RegistrationOffice>
     */
    public function findByRegionCode(string $regionCode): array
    {
        return array_values(array_filter(
            $this->registrationOffices,
            static fn (RegistrationOffice $office): bool => $office->regionCode === $regionCode,
        ));
    }
}
