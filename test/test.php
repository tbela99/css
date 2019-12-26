#!/usr/bin/php
<?php

require 'autoload.php';
use TBela\CSS\Compiler;


// test builder
$compiler = new Compiler();

$compiler->setContent('

p {
	/* hex */
	color: #f00;
}
.img-polaroid {
	padding: 4px;
	background-color: #ffffff;
	background-color: rgba(0, 0, 0, 1);
	border: 1px solid #cccccc;
	border: 1px solid rgba(0, 0, 0, 0.2);
	-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

p {
	/* hsl(.75turn, 60%, 70%) */
	color: hsl(.75turn, 60%, 70%);
}
p {
	/* hex */
	color: red;
}
');

echo $compiler->compile()."\n\n\n";
echo $compiler->setOptions(['compress' => true, 'rgba_hex' => true])->compile()."\n\n\n";