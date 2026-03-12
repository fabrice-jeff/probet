<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class WinnerBallRequest extends  AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $balls;

    // #[Type('string')]
    // public readonly string $balls_two;

    // #[NotBlank]
    // #[Type('string')]
    // public readonly string $country;
}