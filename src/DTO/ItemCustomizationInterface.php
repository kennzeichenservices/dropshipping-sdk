<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

/**
 * Interface for order item customization data transfer objects.
 */
interface ItemCustomizationInterface
{
    /**
     * Convert the item customization to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
