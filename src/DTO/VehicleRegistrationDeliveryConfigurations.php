<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

use Dropshipping\Enums\VehicleRegistrationDeliveryConfigurationDeliveryOption;
use Dropshipping\Exceptions\DropshippingException;

/**
 * The delivery configurations of a vehicle registration: where each of the two
 * registration documents goes once the office has issued it.
 *
 * The API requires both configurations together — there is no way to redirect
 * only the Zulassungsbescheinigung Teil II and leave Teil I unmentioned. Pass
 * {@see VehicleRegistrationDeliveryToOwnerDeliveryConfiguration} for the
 * document that keeps the default.
 *
 * Omitting {@see VehicleRegistrationCustomization::$deliveryConfigurations}
 * altogether sends both documents to the vehicle holder.
 *
 * ## The pickup rule the API enforces but does not publish
 *
 * The two configurations look independent in the 2.4.0 spec — `deliveryOption`
 * is a bare enum and each document carries its own object — but
 * `PICKUP_BY_THIRD_PARTY` is all or nothing:
 *
 * - Both documents are collected, or neither is. Collecting one while the other
 *   is posted is rejected, whether the other goes to the vehicle holder or to a
 *   third party — even to the very same third party.
 * - Both are collected by one and the same party. Two different collectors are
 *   rejected; the office hands the pair over once, to whoever turns up.
 *
 * `DELIVERY_TO_THIRD_PARTY` has neither restriction: it may apply to one
 * document alone, and the two documents may go to different recipients.
 *
 * The API answers a violation with HTTP 400 and a bare
 * `{"error":"Validation error. TraceId: …"}` naming no field, so the rules are
 * enforced here instead — the same reason the undocumented service type code
 * rules live in {@see VehicleRegistrationCustomization}. Source: rejections
 * from the dropshipping API, probed combination by combination; should the API
 * ever relax them, the guards here have to go with them.
 */
final readonly class VehicleRegistrationDeliveryConfigurations
{
    /**
     * @param VehicleRegistrationDeliveryConfigurationInterface $vehicleRegistrationCertificate Where the Zulassungsbescheinigung Teil I (Fahrzeugschein) goes.
     * @param VehicleRegistrationDeliveryConfigurationInterface $vehicleTitle                   Where the Zulassungsbescheinigung Teil II (Fahrzeugbrief) goes.
     *
     * @throws DropshippingException When only one of the two documents is collected by a third party, or when the two collectors differ.
     */
    public function __construct(
        public VehicleRegistrationDeliveryConfigurationInterface $vehicleRegistrationCertificate,
        public VehicleRegistrationDeliveryConfigurationInterface $vehicleTitle,
    ) {
        $certificateIsPickup = $vehicleRegistrationCertificate instanceof VehicleRegistrationPickupByThirdPartyDeliveryConfiguration;
        $titleIsPickup = $vehicleTitle instanceof VehicleRegistrationPickupByThirdPartyDeliveryConfiguration;

        if (!$certificateIsPickup && !$titleIsPickup) {
            return;
        }

        if ($certificateIsPickup !== $titleIsPickup) {
            throw new DropshippingException(sprintf(
                'Delivery option %s applies to both registration documents or to neither: %s is collected '
                . 'from the registration office while %s is not',
                VehicleRegistrationDeliveryConfigurationDeliveryOption::PickupByThirdParty->value,
                $certificateIsPickup ? 'vehicleRegistrationCertificate' : 'vehicleTitle',
                $certificateIsPickup ? 'vehicleTitle' : 'vehicleRegistrationCertificate',
            ));
        }

        // Compared by value, not by identity: the two configurations are usually built
        // separately from the same form input, and the API only cares that the collector
        // it records for the pair is one party. The instanceof pair is repeated rather than
        // reusing the booleans above so that the recipient property is actually in scope.
        if ($vehicleRegistrationCertificate instanceof VehicleRegistrationPickupByThirdPartyDeliveryConfiguration
            && $vehicleTitle instanceof VehicleRegistrationPickupByThirdPartyDeliveryConfiguration
            && $vehicleRegistrationCertificate->recipient->toArray() !== $vehicleTitle->recipient->toArray()
        ) {
            throw new DropshippingException(
                'Both registration documents are collected from the registration office by the same third '
                . 'party, but vehicleRegistrationCertificate and vehicleTitle name different recipients',
            );
        }
    }

    /**
     * Convert the delivery configurations to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'vehicleRegistrationCertificate' => $this->vehicleRegistrationCertificate->toArray(),
            'vehicleTitle' => $this->vehicleTitle->toArray(),
        ];
    }
}
