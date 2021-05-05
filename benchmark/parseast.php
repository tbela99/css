#!/usr/bin/php
<?php

require __DIR__.'/../test/autoload.php';
require_once 'css.php';

use TBela\CSS\Parser;

/**
 * @var string $css
 * @var array $options
 */

$parser = (new Parser($css, $options));

$ast = $parser->getAst();