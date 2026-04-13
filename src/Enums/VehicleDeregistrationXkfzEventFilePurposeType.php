<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the purpose type of a file attached to a vehicle deregistration XKFZ event.
 */
enum VehicleDeregistrationXkfzEventFilePurposeType: string
{
    case Certificate = 'CERTIFICATE';
    case Receipt = 'RECEIPT';
    case Application = 'APPLICATION';
    case Unspecified = 'UNSPECIFIED';
}
