<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Support\Hydrator;

/**
 * Represents the complete cost breakdown in a vehicle deregistration webhook event.
 *
 * Contains the optional KBA cost and optional registration office costs.
 */
final readonly class VehicleDeregistrationCostBreakdown
{
    private const CONTEXT = 'VehicleDeregistrationCostBreakdown';

    /**
     * @param int|null                                              $kbaCost                  KBA processing cost in cents, if available.
     * @param VehicleDeregistrationRegistrationOfficeCosts|null     $registrationOfficeCosts  Registration office costs breakdown, if available.
     */
    public function __construct(
        public ?int $kbaCost,
        public ?VehicleDeregistrationRegistrationOfficeCosts $registrationOfficeCosts,
    ) {
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            kbaCost: Hydrator::optionalInt($data, 'kbaCost', self::CONTEXT),
            registrationOfficeCosts: ($costs = Hydrator::optionalArray($data, 'registrationOfficeCosts', self::CONTEXT)) !== null
                ? VehicleDeregistrationRegistrationOfficeCosts::fromArray($costs)
                : null,
        );
    }
}
