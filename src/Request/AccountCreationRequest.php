<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class AccountCreationRequest extends AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $lastName;


    #[NotBlank]
    #[Type('string')]
    public readonly string $firstName;

    #[NotBlank]
    #[Email]
    public readonly string $email;

    #[NotBlank]
    #[Type('string')]
    public readonly string $country;

    #[NotBlank]
    #[Type('string')]
    public readonly string $password;

    #[NotBlank]
    #[Type('string')]
    public readonly string $passwordConfirmation;

    #[Type('string')]
    public readonly string|null $roles;
}