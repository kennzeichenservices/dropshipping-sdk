<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\DTO\Address;
use Dropshipping\Enums\VehicleRegistrationVehicleHolderType;

/**
 * Interface for the holders a vehicle registration can be ordered for.
 *
 * API 2.7.0 discriminates the holder by a `type` property and accepts a legal
 * person besides the natural person that was the only option before. Older API
 * versions do not know the property at all, so the SDK ships three
 * implementations and lets the payload follow from the class the caller picks:
 *
 * - {@see VehicleRegistrationVehicleHolder}: a natural person *without* `type`,
 *   the payload every version up to 2.6.0 expects. Unchanged, so integrations
 *   written against an earlier SDK keep sending exactly what they sent before.
 * - {@see VehicleRegistrationNaturalPersonVehicleHolder}: the same holder *with*
 *   `type: NATURAL_PERSON`, required from 2.7.0 on.
 * - {@see VehicleRegistrationLegalPersonVehicleHolder}: a legal person, 2.7.0
 *   and up.
 *
 * {@see holderType()} returning null is what tells the request DTO that the
 * holder predates the discriminator, and it is the one implementation whose
 * payload carries no type.
 */
interface VehicleRegistrationVehicleHolderInterface
{
    /**
     * The discriminator value identifying this kind of holder, or null for the
     * pre-2.7.0 holder that does not send one.
     */
    public function holderType(): ?VehicleRegistrationVehicleHolderType;

    /**
     * The address of the holder.
     */
    public function address(): Address;

    /**
     * The major customer ID of the customs administration, if the holder has one.
     *
     * Only a legal person can carry one. Where it is set it replaces the SEPA
     * direct debit mandate for the motor vehicle tax, which is why
     * {@see VehicleRegistrationRequest} reads it to decide whether the
     * customization may carry an IBAN.
     */
    public function customsMajorCustomerId(): ?string;

    /**
     * Convert the holder to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
