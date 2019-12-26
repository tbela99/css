CSS (A CSS parser and minifier written in PHP)

---

![Current version](https://img.shields.io/badge/dynamic/json?label=current%20version&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json) [![Documentation](https://img.shields.io/badge/dynamic/json?label=Documentation&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json)](https://tbela99.github.io/css) [![Known Vulnerabilities](https://snyk.io/test/github/tbela99/gzip/badge.svg)](https://snyk.io/test/github/tbela99/css) 

A CSS parser, beautifier and minifier written in PHP. It supports the following features

- remove (nested) empty rules
- merge duplicate rules
- remove duplicate declarations
- process @import directive
- remove @charset directive
- compute css declarations (margin, padding, border-width, border-radius)

This was originally a PHP port of https://github.com/reworkcss/css

## Installation

install using [Composer](https://getcomposer.org/)

```bash
$ composer require tbela99/css
```

## Usage:

```css
h1 {
        color: green;
        color: blue;
        color: black;
    }

    h1 {
        color: #000;
        color: aliceblue;
    }
```

Parse the css file and generate the AST

```php

$parser = new \TBela\CSS\Parser($css);
$ast = $parser->parse();

file_put_contents('style.json', json_encode($ast));
```

Load the AST and generate css code

```php

$ast = json_decode(file_get_contents('style.json'));

$compiler = new \TBela\CSS\Compiler([
    'rgba_hex' => true,
    'compress' => true, // minify the output
    'remove_empty_nodes' => true // remove empty css classes
]);

$css = $parser->compile($ast);

```

pretty print output

```css
h1 {
 color: aliceblue
}
```

minified output

```css
h1{color:#f0f8ff}
```

## CSS manipulation

### Example: Extract Font-src

CSS source
```css
@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body{
background-color: green;
color: #fff;
font-family: Arial, Helvetica, sans-serif;
}
h1{
color: #fff;
font-size: 50px;
font-family: Arial, Helvetica, sans-serif;
font-weight: bold;
}

@media print {
	@font-face {
  font-family: MaHelvetica;
  src: local("Helvetica Neue Bold"),
       local("HelveticaNeue-Bold"),
       url(MgOpenModernaBold.ttf);
  font-weight: bold;
}
body {
  font-family: "Bitstream Vera Serif Bold", serif;
}
p{
font-size: 12px;
color: #000;
text-align: left
}

    @font-face {
        font-family: Arial, MaHelvetica;
        src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
        font-weight: bold;
    }
	}
```

php source
```php 

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\ElementAtRule;
use \TBela\CSS\ElementStylesheet;

$parser = new Parser(file_get_contents('./css/manipulate.css'), [
    'silent' => false,
    'flatten_import' => true
]);

$stylesheet = new ElementStylesheet();

function getNodes ($data, $stylesheet) {

    $nodes = [];

    foreach ($data as $node) {

        if ($node instanceof ElementAtRule) {

            switch ($node->getName()) {

                case 'font-face':

                    foreach ($node as $declaration) {

                        if ($declaration['name'] == 'src') {

                            $stylesheet->append($declaration->copy()->getRoot());
                            break;
                        }
                    }

                    break;

                case 'media':

                    getNodes($node, $stylesheet);
                    break;
            }
        }
    }
}

getNodes ($parser->parse(), $stylesheet);

// deduplicate rules
$stylesheet = Element::getInstance($parser->deduplicate($stylesheet));

echo $stylesheet;
```

result

```css 
@font-face {
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff")
}
@media print {
 @font-face {
   src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf)
 }
}
```

## Build a css document

```php

use \TBela\CSS\ElementStylesheet;

$stylesheet = new ElementStylesheet();

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('background-color', 'white');
$rule->addDeclaration('color', 'black');

echo $stylesheet;

```
output
```css
div {
 background-color: white;
 color: black
}
```
```php

$media = $stylesheet->addAtRule('media', 'print');
$media->append($rule);

```
output
```css
@media print {
  div {
   background-color: white;
   color: black
 }
}
```

```php
$rule = $stylesheet->addRule('div');

$rule->addDeclaration('max-width', '100%');
$rule->addDeclaration('border-width', '0px');

```
output
```css
@media print {
  div {
   background-color: white;
   color: black
 }
}
div {
 max-width: 100%;
 border-width: 0px
}
```

```php

$media->append($rule);

```
output
```css
@media print {
  div {
   background-color: white;
   color: black
 }
  div {
   max-width: 100%;
   border-width: 0px
 }
}
```

```php

$stylesheet->insert($rule, 0);
```
output
```css
div {
 max-width: 100%;
 border-width: 0px
}
@media print {
  div {
   background-color: white;
   color: black
 }
}
```