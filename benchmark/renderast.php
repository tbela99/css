#!/usr/bin/php
<?php

use TBela\CSS\Renderer;

ob_start();
require_once 'parseast.php';
ob_clean();

/**
 * @var \TBela\CSS\Parser $parser
 * @var array $options
 */

echo (new Renderer($options))->renderAst($parser);