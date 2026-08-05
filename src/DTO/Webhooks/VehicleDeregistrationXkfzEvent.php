<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleDeregistrationXkfzEventStatus;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when a vehicle deregistration XKFZ status update is received.
 *
 * Contains the order reference, the current processing status, optional attached
 * files and an optional cost breakdown detailing KBA and registration office charges.
 */
final readonly class VehicleDeregistrationXkfzEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleDeregistrationXkfzEvent';

    /**
     * @param string                                           $eventTime     The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                     $order         The associated order reference.
     * @param VehicleDeregistrationXkfzEventStatus             $status        The current processing status of the deregistration.
     * @param string                                           $derivedStatus A derived human-readable status string.
     * @param list<VehicleDeregistrationXkfzEventFile>|null    $files         Files attached to this event, if any.
     * @param VehicleDeregistrationCostBreakdown|null          $costBreakdown The cost breakdown, if available.
     * @param list<VehicleDeregistrationXkfzEventMessage>|null $messages      Messages attached to this event, if any.
     * @param string|null                                      $applicationId The XKFZ application ID, if available.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public VehicleDeregistrationXkfzEventStatus $status,
        public string $derivedStatus,
        public ?array $files,
        public ?VehicleDeregistrationCostBreakdown $costBreakdown,
        public ?array $messages,
        public ?string $applicationId,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleDeregistrationXkfzEvent;
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
                VehicleDeregistrationXkfzEventStatus::class,
                $data,
                'status',
                self::CONTEXT,
                VehicleDeregistrationXkfzEventStatus::Unknown,
            ),
            derivedStatus: Hydrator::requireString($data, 'derivedStatus', self::CONTEXT),
            files: isset($data['files'])
                ? array_map(
                    static fn (array $file): VehicleDeregistrationXkfzEventFile => VehicleDeregistrationXkfzEventFile::fromArray($file),
                    Hydrator::requireArrayList($data, 'files', self::CONTEXT),
                )
                : null,
            costBreakdown: ($costs = Hydrator::optionalArray($data, 'costBreakdown', self::CONTEXT)) !== null
                ? VehicleDeregistrationCostBreakdown::fromArray($costs)
                : null,
            messages: isset($data['messages'])
                ? array_map(
                    static fn (array $msg): VehicleDeregistrationXkfzEventMessage => VehicleDeregistrationXkfzEventMessage::fromArray($msg),
                    Hydrator::requireArrayList($data, 'messages', self::CONTEXT),
                )
                : null,
            applicationId: Hydrator::optionalString($data, 'applicationId', self::CONTEXT),
        );
    }
}
