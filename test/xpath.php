#!/bin/php
<?php

require 'autoload.php';

/**
 * [@name] or attribute(@name) -> get every node that has a name (every node)
 * [@name=@font-face]/[@name=src] -> get @font-face node
 * [@name=@font-face]/.. -> get @font-face parent node
 * / -> get the root node
 * * -> get every node
 * selector(div, .bar) or div, .bar -> get nodes that contain selector div or .bar
 * selector(div .bar) or div .bar -> get nodes that contain selector "div .bar"
 * selector(div, .bar) ~ * -> get all next sibling of nodes that contain div or .bar
 * selector(div, .bar) ~ [2] -> get the next sibling at position 2 of the nodes that contain div or .bar
 * selector(div, .bar) + p / [@name=src] -> get node that contain div or .bar, select the next rule that contains p and select its child with name src
 * selector(div, .bar)/[@name=src]
 * selector(div, .bar)/[contains(@name,"src")]
 */

use TBela\CSS\Compiler;
use TBela\CSS\Element;
use TBela\Css\Element\AtRule;
use TBela\Css\Element\Rule;
use TBela\CSS\Parser;
use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;
use TBela\CSS\RuleList;

?>
Expression = root
Expression = selector
Expression = attribute
expression = root [path selector[attribute]*]*
path = / or //

selector = . or .. or * or comment() or tokens [separator token]*
tokens = token [separator token]*
separator = space or ~ or , or +
attribute = '[' number or function or Expression_attribute ']'
Expression_attribute = @string or @string operator token
operator = != or =
@string = @[a-zA-Z-_][a-zA-Z-_0-9]*
<?php

#$expression = 'div, .bar ~ [2]';
#$expression = '//a, form    input \\[type="text"\\]';
#$expression = 'input [type=text\\]';
#$expression = '//input';
#$expression = '//';
#$expression = '/.field, a, #id/background';
#$expression = '/.field p, a >   span, div#id span.can /background[2]';
#$expression='[not(contains(@name, "background"))]';

$expression='h1,a';
#$expression='[contains(@name, "background")]';
#$expression = '@media[@value^=print][2],p[1]';
#$expression = '@media[@value^=print][1]';
#$expression = '@media[@value$=\'print\']';
#$expression = '@media[@value$=print]';
#$expression = '@media[@value*=print]';
#$expression = '@media[@value^=print]';
#$expression = '@media[@value=print],p';
#$expression = '@media[@value=print]';
#$expression = './[@value=print]';
#$expression = '// @font-face / src / ..';
#$expression = '//* / color/ ..';

echo "parse expression $expression\n\n";

$element = (new Compiler())->setContent('@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
h1,h2, a {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}
}')->getData();

echo var_export(array_map('trim', $element->query($expression)), true);
//$stylesheet = (new Parser($css))->parse();


//var_dump(parse($expression));