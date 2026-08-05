<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleRegistrationXkfzEventStatus;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when a vehicle registration XKFZ status update is received.
 *
 * Contains the order reference, the current processing status, optional attached
 * files, an optional cost breakdown detailing KBA and registration office charges,
 * and — once the registration is approved — the license plate assigned to the vehicle.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationXkfzEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationXkfzEvent';

    /**
     * @param string                                        $eventTime     The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                  $order         The associated order reference.
     * @param VehicleRegistrationXkfzEventStatus            $status        The current processing status of the registration.
     * @param string                                        $derivedStatus A derived human-readable status string.
     * @param list<VehicleRegistrationXkfzEventFile>|null    $files         Files attached to this event, if any.
     * @param VehicleRegistrationCostBreakdown|null         $costBreakdown The cost breakdown, if available.
     * @param list<VehicleRegistrationXkfzEventMessage>|null $messages      Messages attached to this event, if any.
     * @param string|null                                   $applicationId The XKFZ application ID, if available.
     * @param VehicleRegistrationXkfzEventLicensePlate|null $licensePlate  The plate assigned to the vehicle, if already known.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public VehicleRegistrationXkfzEventStatus $status,
        public string $derivedStatus,
        public ?array $files,
        public ?VehicleRegistrationCostBreakdown $costBreakdown,
        public ?array $messages,
        public ?string $applicationId,
        public ?VehicleRegistrationXkfzEventLicensePlate $licensePlate,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleRegistrationXkfzEvent;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventTime(): string
    {
        return $this->eventTime;
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
            eventTime: Hydrator::requireString($data, 'eventTime', self::CONTEXT),
            order: WebhookOrder::fromArray(Hydrator::requireArray($data, 'order', self::CONTEXT)),
            // The API may introduce statuses before this SDK models them. Falling back to
            // Unknown keeps an otherwise valid event deliverable instead of throwing.
            status: Hydrator::requireEnum(
                VehicleRegistrationXkfzEventStatus::class,
                $data,
                'status',
                self::CONTEXT,
                VehicleRegistrationXkfzEventStatus::Unknown,
            ),
            derivedStatus: Hydrator::requireString($data, 'derivedStatus', self::CONTEXT),
            files: isset($data['files'])
                ? array_map(
                    static fn (array $file): VehicleRegistrationXkfzEventFile => VehicleRegistrationXkfzEventFile::fromArray($file),
                    Hydrator::requireArrayList($data, 'files', self::CONTEXT),
                )
                : null,
            costBreakdown: ($costs = Hydrator::optionalArray($data, 'costBreakdown', self::CONTEXT)) !== null
                ? VehicleRegistrationCostBreakdown::fromArray($costs)
                : null,
            messages: isset($data['messages'])
                ? array_map(
                    static fn (array $msg): VehicleRegistrationXkfzEventMessage => VehicleRegistrationXkfzEventMessage::fromArray($msg),
                    Hydrator::requireArrayList($data, 'messages', self::CONTEXT),
                )
                : null,
            applicationId: Hydrator::optionalString($data, 'applicationId', self::CONTEXT),
            licensePlate: ($plate = Hydrator::optionalArray($data, 'licensePlate', self::CONTEXT)) !== null
                ? VehicleRegistrationXkfzEventLicensePlate::fromArray($plate)
                : null,
        );
    }
}
