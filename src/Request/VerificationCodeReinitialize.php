<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class VerificationCodeReinitialize extends AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $code;

    #[NotBlank]
    #[Type('string')]
    public readonly string $email;
}