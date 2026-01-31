<?php

declare(strict_types=1);

namespace Dropshipping\Enums;

enum Gender: string
{
    case Female = 'FEMALE';
    case Male = 'MALE';
    case Unspecified = 'UNSPECIFIED';
}
