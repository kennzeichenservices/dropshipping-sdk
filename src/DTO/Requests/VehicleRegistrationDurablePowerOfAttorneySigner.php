<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Requests;

use Dropshipping\Enums\Gender;
use Dropshipping\Exceptions\DropshippingException;
use Dropshipping\Support\Validator;

/**
 * The person who signs a durable power of attorney on behalf of the company.
 *
 * The signing invitation goes to {@see $email}, so it has to reach that person
 * and not a shared mailbox.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneySigner
{
    /**
     * @param string $firstName First name, 1–100 characters.
     * @param string $lastName  Last name, 1–100 characters.
     * @param Gender $gender    Gender.
     * @param string $email     Email address, 3–255 characters.
     *
     * @throws DropshippingException When a value violates the API constraints.
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public Gender $gender,
        public string $email,
    ) {
        Validator::requireStringLength($firstName, 'signer.firstName', 1, 100);
        Validator::requireStringLength($lastName, 'signer.lastName', 1, 100);
        Validator::requireStringLength($email, 'signer.email', 3, 255);
        Validator::requireEmail($email, 'signer.email');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'gender' => $this->gender->value,
            'email' => $this->email,
        ];
    }
}
