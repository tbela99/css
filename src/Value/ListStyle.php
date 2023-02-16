<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class ListStyle extends ShortHand
{

    public static array $keywords = ['none'];

    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        'keyword',
        [
			['type' => 'list-style-image', 'optional' => true],
			['type' => 'list-style-position', 'optional' => true],
			['type' => 'list-style-type', 'optional' => true]
        ]
    ];
}
