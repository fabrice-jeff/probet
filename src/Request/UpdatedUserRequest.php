<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class UpdatedUserRequest extends  AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $lastName;

    #[NotBlank]
    #[Type('string')]
    public readonly string $firstName;

    #[NotBlank]
    #[Type('string')]
    public readonly string $country;

    #[NotBlank]
    #[Type('string')]
    public readonly string $phoneNumber;


}