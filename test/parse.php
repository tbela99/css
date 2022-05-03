#!/usr/bin/php
<?php

use TBela\CSS\Parser;

require 'autoload.php';


echo new Parser('.foo { name: "attr "');