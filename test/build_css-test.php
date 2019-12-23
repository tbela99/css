#!/usr/bin/php
<?php

set_time_limit(1);

require 'autoload.php';

use \TBela\CSS\ElementStylesheet;
$step = 0;
$stylesheet = new ElementStylesheet();

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('background-color', 'white');
$rule->addDeclaration('color', 'black');

file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);

$media = $stylesheet->addAtRule('media', 'print');
$media->append($rule);

file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('max-width', '100%');
$rule->addDeclaration('border-width', '0px');

echo $stylesheet."\n\n\n";

file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);

$media->append($rule);

echo $stylesheet."\n\n\n";

file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);

$stylesheet->insert($rule, 0);

echo $stylesheet."\n\n\n";

file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);

$rule->addSelector('.name, .general');

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


$rule->removeSelector('div, .general');

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


$rule['selector'] = 'a,b,strong';

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


$media['value'] = 'all';

$rule2 = $media->addRule('.new');
$rule2->addDeclaration('color', 'green');

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


$stylesheet->insert($rule2, 1);

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


$rule->merge($rule2);

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


$rule2['parent']->remove($rule2);

echo $stylesheet."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);


echo $rule2."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $rule2);


echo $rule."\n\n\n";
file_put_contents('output/build_css_'.(++$step).'.css', $rule);


$rule3 = $media->addRule('ul');

$rule3->addDeclaration('margin', '0px');
$rule3->addDeclaration('padding', '5px 3px');

foreach ($media as $key => $child) {

    echo $child."\n\n\n";

    file_put_contents('output/build_css_'.(++$step).'.css', $child);
}


file_put_contents('output/build_css_'.(++$step).'.css', $stylesheet);
