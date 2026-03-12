<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class BalanceRequest extends  AbstractJsonRequest
{
    #[NotBlank]
    #[Type('string')]
    public readonly string $period;
}