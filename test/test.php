#!/usr/bin/php
<?php

require 'autoload.php';

use \TBela\CSS\Value;


//echo Value::parse('"\\u0001" local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
//      url(MgOpenModernaBold.ttf)');
echo Value::parse('"\\u0001" local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"), url(css/MgOpenModernaBold.ttf)');