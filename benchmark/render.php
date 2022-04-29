#!/usr/bin/php
<?php

use TBela\CSS\Renderer;

ob_start();
require_once 'parse.php';
ob_clean();

/**
 * @var string $stylesheet
 * @var array $options
 */

echo (new Renderer($options))->render($stylesheet);