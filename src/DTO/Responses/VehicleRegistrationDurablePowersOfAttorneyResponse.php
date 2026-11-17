<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Responses;

use Dropshipping\Support\Hydrator;

/**
 * Response DTO for the listing of durable powers of attorney. Requires API
 * 2.7.0.
 *
 * The listing is not paginated and includes revoked and failed entries, so
 * filter with {@see usable()} when picking one for an order.
 */
final readonly class VehicleRegistrationDurablePowersOfAttorneyResponse
{
    private const CONTEXT = 'VehicleRegistrationDurablePowersOfAttorneyResponse';

    /**
     * @param list<VehicleRegistrationDurablePowerOfAttorney> $durablePowersOfAttorney The powers of attorney.
     */
    public function __construct(
        public array $durablePowersOfAttorney,
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
            durablePowersOfAttorney: array_map(
                static fn (array $item): VehicleRegistrationDurablePowerOfAttorney => VehicleRegistrationDurablePowerOfAttorney::fromArray($item),
                Hydrator::requireArrayList($data, 'durablePowersOfAttorney', self::CONTEXT),
            ),
        );
    }

    /**
     * The power of attorney with the given ID, or null if the listing has none.
     *
     * The comparison ignores letter case, since a UUID means the same in either.
     */
    public function findById(string $id): ?VehicleRegistrationDurablePowerOfAttorney
    {
        foreach ($this->durablePowersOfAttorney as $powerOfAttorney) {
            if (strcasecmp($powerOfAttorney->id, $id) === 0) {
                return $powerOfAttorney;
            }
        }

        return null;
    }

    /**
     * Every power of attorney that is signed, not revoked and not expired on the
     * given day. See {@see VehicleRegistrationDurablePowerOfAttorney::isUsable()}.
     *
     * @param \DateTimeInterface|null $today The day to check against, defaults to today.
     *
     * @return list<VehicleRegistrationDurablePowerOfAttorney>
     */
    public function usable(?\DateTimeInterface $today = null): array
    {
        return array_values(array_filter(
            $this->durablePowersOfAttorney,
            static fn (VehicleRegistrationDurablePowerOfAttorney $powerOfAttorney): bool => $powerOfAttorney->isUsable($today),
        ));
    }
}
