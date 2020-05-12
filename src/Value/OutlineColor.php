<?php

namespace TBela\CSS\Value;

use \Exception;
use \TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class OutlineColor extends Color
{
    use ValueTrait;

    /**
     * @inheritDoc
     */
    public function matchToken($token, $previousToken = null, $previousValue = null)
    {
        return $token->type == 'color';
    }
}
