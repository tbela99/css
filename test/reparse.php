#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Parser;


echo new Parser('.a{color:rgba(var(--cassiopeia-color-primary), .25);
background-color: rgba(255, 255, 255, 1);
}');