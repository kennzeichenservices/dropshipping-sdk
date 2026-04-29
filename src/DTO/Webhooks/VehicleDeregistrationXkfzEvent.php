<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleDeregistrationXkfzEventStatus;
use Dropshipping\Enums\WebhookEventType;

/**
 * Webhook event fired when a vehicle deregistration XKFZ status update is received.
 *
 * Contains the order reference, the current processing status, optional attached
 * files and an optional cost breakdown detailing KBA and registration office charges.
 */
final readonly class VehicleDeregistrationXkfzEvent implements WebhookEventInterface
{
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
            eventTime: $data['eventTime'],
            order: WebhookOrder::fromArray($data['order']),
            status: VehicleDeregistrationXkfzEventStatus::from($data['status']),
            derivedStatus: $data['derivedStatus'],
            files: isset($data['files'])
                ? array_map(
                    static fn (array $file): VehicleDeregistrationXkfzEventFile => VehicleDeregistrationXkfzEventFile::fromArray($file),
                    $data['files'],
                )
                : null,
            costBreakdown: isset($data['costBreakdown'])
                ? VehicleDeregistrationCostBreakdown::fromArray($data['costBreakdown'])
                : null,
            messages: isset($data['messages'])
                ? array_map(
                    static fn (array $msg): VehicleDeregistrationXkfzEventMessage => VehicleDeregistrationXkfzEventMessage::fromArray($msg),
                    $data['messages'],
                )
                : null,
            applicationId: $data['applicationId'] ?? null,
        );
    }
}
