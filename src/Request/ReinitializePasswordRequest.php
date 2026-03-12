<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ReinitializePasswordRequest extends  AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $email;


    #[NotBlank]
    #[Type('string')]
    public readonly string $password;


    #[NotBlank]
    #[Type('string')]
    public readonly string $passwordConfirmation;



}