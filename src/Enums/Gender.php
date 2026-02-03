<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

/**
 * Represents the gender of a person in the dropshipping system.
 */
enum Gender: string
{
    case Female = 'FEMALE';
    case Male = 'MALE';
    case Unspecified = 'UNSPECIFIED';
}
