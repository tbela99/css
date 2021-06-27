#!/usr/bin/php
<?php

use TBela\CSS\Renderer;

ob_start();
require_once 'parseast.php';
ob_clean();

/**
 * @var stdClass $ast
 * @var array $options
 */

echo (new Renderer($options))->renderAst($ast);