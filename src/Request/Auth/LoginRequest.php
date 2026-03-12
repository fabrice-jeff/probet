<?php

namespace App\Request\Auth;

use App\Request\AbstractJsonRequest;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class LoginRequest extends AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $email;

    #[NotBlank]
    #[Type('string')]
    public readonly string $password;
}