<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class PlayRequest extends  AbstractJsonRequest
{

    #[NotBlank]
    #[Type('string')]
    public readonly string $balls;

    #[NotBlank]
    #[Type('string')]
    public readonly string $amount;

    #[NotBlank]
    #[Type('string')]
    public readonly string $typeBet;

    #[NotBlank]
    #[Type('string')]
    public readonly string $numberDraw;

    #[Type('bool')]
    public readonly bool $doubleChance;

    #[NotBlank]
    #[Type('bool')]
    public readonly bool $country;
}