#!/usr/bin/php
<?php

require __DIR__.'/../test/autoload.php';
require_once 'css.php';

use TBela\CSS\Renderer;


/**
 * @var string $css
 * @var string $filename
 * @var array $options
 */

echo Renderer::fromString($css, $options, $options);