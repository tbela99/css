#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

require 'autoload.php';

echo (string) new Parser('.a {
    --theme-primary-350:rgb(calc(51 + var(--theme-primary-color-r) * .8), calc(51 + var(--theme-primary-color-g) * .8), calc(51 + var(--theme-primary-color-b) * .8));
}
');

