<?php

namespace TBela\CSS\Value;

/**
 * Css string value
 * @package TBela\CSS\Value
 */
class BackgroundAttachment extends ShortHand
{

    protected static array $keywords = [
        'fixed',
        'local',
        'scroll'
    ];

    protected static array $defaults = ['scroll'];

    /**
     * @var array
     * @ignore
     */
    protected static array $patterns = [

        'keyword'
    ];
}
