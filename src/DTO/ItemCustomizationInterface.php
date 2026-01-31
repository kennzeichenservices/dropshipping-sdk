<?php

declare(strict_types=1);

namespace Dropshipping\DTO;

interface ItemCustomizationInterface
{
    /** @return array<string, mixed> */
    public function toArray(): array;
}
