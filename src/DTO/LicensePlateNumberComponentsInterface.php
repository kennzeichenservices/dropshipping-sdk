<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

/**
 * Interface for license plate number component data transfer objects.
 */
interface LicensePlateNumberComponentsInterface
{
    /**
     * Convert the license plate number components to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
