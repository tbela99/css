#!/usr/bin/php
<?php

require 'autoload.php';

use TBela\CSS\Element\AtRule;
use TBela\CSS\Parser;
use \TBela\CSS\Renderable;
use \TBela\CSS\Element\Declaration;
use \TBela\CSS\Property\PropertyInterface;
use \TBela\CSS\Renderer;
use \TBela\CSS\Compiler;
use TBela\CSS\Value;
use TBela\CSS\Value\CSSFunction;

$parser = (new Parser())->setOptions(['flatten_import' => true])->setContent('@font-face {
  font-family: "Bitstream Vera Serif Bold", "Arial", "Helvetica";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}
.pic {
background: no-repeat url("imgs/lizard.png");
}
.element {
background-image: url("imgs/lizard.png"),
                  url("imgs/star.png");
}');

$parser2 = (new Parser())->setContent('
p {
	/* Functional syntax with floats value */
	color: rgba(255, 0, 153.6, 1);
	color: rgba(1e2, .5e1, .5e0, +.25e2%);
}

p {
	/* These examples all specify the same color: black. */
	/* color black black */
	color: black;
}
p {
	/* color black #000 */
	color: #000;
}
p {
	/* color black #000000 */
	color: #000000;
}
p {
	/* color black rgb(0, 0, 0) */
	color: rgb(0, 0, 0);
}
p {
	/* color black rgba(0, 0, 0, 1) */
	color: rgba(0, 0, 0, 1);
}
p {
	/* color black #000000ff */
	color: #000000ff;
}
p {
	/* These examples all specify the same color: red. */
	/* color red red */
	color: red;
}
p {
	/* color red #f00 */
	color: #f00;
}
p {
	/* color red #f00F */
	color: #f00F;
}
p {
	/* color red #ff0000Ff */
	color: #ff0000Ff;
}
p {
	/* color red #ff0000 */
	color: #ff0000;
}
p {
	/* color red rgb(255,0,0) */
	color: rgb(255,0,0);
}
p {
	/* color red rgba(255,0,0,1); */
	color: rgba(255,0,0,1);
}
p {
	/* color red rgb(100%, 0%, 0%) */
	color: rgb(100%, 0%, 0%);
}
p {
	/* color red hsl(0, 100%, 50%) */
	color: hsl(0, 100%, 50%);
}
p {
	/* red 50% translucent #ff000080 */
	color: #ff000080;
}
p {
	/* red 50% translucent rgba rgba(255, 0, 0, 0.5) */
	color: rgba(255, 0, 0, 0.5);
}
p {
	/* red 50% translucent hsla hsl(0, 100%, 50%, 0.5) */
	color: hsl(0, 100%, 50%, 0.5);
}

p {

	/* red 50% translucent hsla(0, 100%, 50%, 0.5) */
	color: hsla(0, 100%, 50%, 0.5);
}

p {
	/* These examples all specify the same color: a lavender. */
	/* lavender hsl(270,60%,70%) */
	color: hsl(270,60%,70%);
}
p {
	/* lavender hsl(270, 60%, 70%) */
	color: hsl(270, 60%, 70%);
}
p {
	/* lavender hsl(270 60% 70%) */
	color: hsl(270 60% 70%);
}
p {
	/* lavender hsl(270deg, 60%, 70%) */
	color: hsl(270deg, 60%, 70%);
}
p {
	/* lavender hsl(4.71239rad, 60%, 70%) */
	color: hsl(4.71239rad, 60%, 70%);
}
p {
	/* lavender hsl(.75turn, 60%, 70%) */
	color: hsl(.75turn, 60%, 70%);
}

p {
	/* lavender hsl(.75turn, 60%, 70%, 1) */
	color: hsl(.75turn, 60%, 70%, 1);
}





p {
	/* These examples all specify the same color: a lavender that is 15% opaque. */
	/* lavender that is 15% opaque. hsl(270, 60%, 50%, .15) */
	color: hsl(270, 60%, 50%, .15);
}
p {
	color: hsl(270, 60%, 50%, 15%);
	/* lavender that is 15% opaque. hsl(270, 60%, 50%, 15%) */
}
p {
	color: hsl(270 60% 50% / .15);
	/* lavender that is 15% opaque. hsl(270 60% 50% / .15) */
}
p {
	/* lavender that is 15% opaque. hsl(270 60% 50% / 15%) */
	color: hsl(270 60% 50% / 15%);
}



.pic {
background: no-repeat url("imgs/lizard.png");
}
.element {
background-image: url("imgs/lizard.png"),
                  url("imgs/star.png");
}
');

//$parser->merge((new Parser())->load('./css/atrules.css'));
//$parser->merge($parser2);
//$parser->merge((new Parser())->load('http://localhost/1nG/29hyun/cache/z/127.0.0.1/css/uq8Jy.css'));

$renderer = new Renderer();

var_dump($renderer->render((new Parser())->load('tmp/uq8Jy.css')->parse()));
//var_dump($renderer->render((new Parser())->load('http://localhost/1nG/29hyun/cache/z/127.0.0.1/css/uq8Jy.css')->parse()));

//var_dump($renderer->render($parser->parse()));

