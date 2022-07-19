#!/usr/bin/php
<?php

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

require 'autoload.php';

echo (string) new Parser('.a {
    color:rgba(255,0,153.6,1)
}
');