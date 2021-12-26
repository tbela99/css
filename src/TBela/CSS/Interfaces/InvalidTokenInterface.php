<?php

namespace TBela\CSS\Interfaces;
use TBela\CSS\Value;

/**
 * Interface implemented by Elements
 */
interface InvalidTokenInterface {

    public function recover($property = null): Value;
}