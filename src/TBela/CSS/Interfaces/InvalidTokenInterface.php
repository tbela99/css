<?php

namespace TBela\CSS\Interfaces;
use TBela\CSS\Value;

/**
 * Interface implemented by Elements
 */
interface InvalidTokenInterface {

    /**
     * attempt to return a valid token
     * @param string|Value|null|Set $property
     * @return Value
     */
    public function recover($property = null): Value;
}