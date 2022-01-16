<?php

namespace TBela\CSS\Parser\Validator;

use TBela\CSS\Parser\SyntaxError;

trait ValidatorTrait
{

    protected ?string $error = null;

    public function getError(): ?string {

        return $this->error;
    }
}