<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class TextDecorationStyle  extends Keyword
{

    protected static array $keywords = [
        'solid',
        'double',
        'dotted',
        'dashed',
        'wavy',
		'inherit',
		'initial',
		'revert',
		'revert-layer',
		'unset'
    ];
}
