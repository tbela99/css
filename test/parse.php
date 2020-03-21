#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Compiler;

$compiler = new Compiler(['compress' => true, 'rgba_hex' => false]);
//

$data = [];

$data[] = [
    'p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
}', 'p {
 /* Functional syntax with floats value */
 color: #f09
}'];

$data[] = [
    'p {
	/* Functional syntax with floats value */
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}', 'p {
 /* Functional syntax with floats value */
 color: rgba(100, 5, 0.5, 0.25)
}'];

$data[] = [
    '
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}', 'p {
 /* red 50% translucent #ff000080 */
 color: rgba(255, 0, 0, .5)
}'];

$data[] = [
    'p {
	/* red 50% translucent rgba rgba(255, 0, 0, 0.5) */
	color: rgba(255, 0, 0, 0.5);
}', 'p {
 /* red 50% translucent rgba rgba(255, 0, 0, 0.5) */
 color: rgba(255, 0, 0, 0.5)
}'];

$data[] = [
    'p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, 0.5) */
	color: hsl(0, 100%, 50%, 0.5);
}', 'p {
 /* red 50% translucent hsla hsl(0, 100%, 50%, 0.5) */
 color: hsla(0, 100%, 50%, 0.5)
}'];

$data[] = [
    'p {

	/* red 50% translucent hsla(0, 100%, 50%, 0.5) */
	color: hsla(0, 100%, 50%, 0.5);
}', 'p {
 /* red 50% translucent hsla(0, 100%, 50%, 0.5) */
 color: hsla(0, 100%, 50%, 0.5)
}'];

foreach ($data as $values) {

    echo $compiler->setContent($values[0])->compile()."\n\n";
}
