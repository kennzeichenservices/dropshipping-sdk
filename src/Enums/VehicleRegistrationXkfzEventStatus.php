<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the processing status of a vehicle registration XKFZ event.
 *
 * Separate enum from {@see VehicleDeregistrationXkfzEventStatus} because the API
 * spec defines distinct status schemas per context, even though both currently
 * carry the same values.
 */
enum VehicleRegistrationXkfzEventStatus: string
{
    case Accepted = 'ACCEPTED';
    case Approved = 'APPROVED';
    case ApprovedWithDocuments = 'APPROVED_WITH_DOCUMENTS';
    case Failed = 'FAILED';
    case Forwarded = 'FORWARDED';
    case Processed = 'PROCESSED';
    case Rejected = 'REJECTED';
    case RejectedWithDocuments = 'REJECTED_WITH_DOCUMENTS';
    case Unknown = 'UNKNOWN';
}
