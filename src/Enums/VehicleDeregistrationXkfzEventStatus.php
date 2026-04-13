<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the processing status of a vehicle deregistration XKFZ event.
 */
enum VehicleDeregistrationXkfzEventStatus: string
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
