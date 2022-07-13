CSS (A CSS parser and minifier written in PHP)

---

[![CI](https://github.com/tbela99/css/actions/workflows/php.yml/badge.svg)](https://github.com/tbela99/css/actions/workflows/php.yml) ![Current version](https://img.shields.io/badge/dynamic/json?label=current%20version&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fpackage.json) [![Packagist](https://poser.pugx.org/tbela99/css/downloads)](https://packagist.org/packages/tbela99/css) [![Documentation](https://img.shields.io/badge/dynamic/json?label=documentation&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fpackage.json)](https://tbela99.github.io/css) [![Known Vulnerabilities](https://snyk.io/test/github/tbela99/gzip/badge.svg)](https://snyk.io/test/github/tbela99/css)

A CSS parser, beautifier and minifier written in PHP. It supports the following features

## Features

- multibyte characters encoding
- sourcemap
- CSS Nesting module
- partially implemented CSS Syntax module level 3
- partial CSS validation
- CSS colors module level 4
- parse and render CSS
- optimize css:
  - merge duplicate rules
  - remove duplicate declarations
  - remove empty rules
  - compute css shorthand (margin, padding, outline, border-radius, font, background)
  - process @import document to reduce the number of HTTP requests
  - remove @charset directive
- query api with xpath like or class name syntax
- traverser api to transform the css and ast

## Installation

install using [Composer](https://getcomposer.org/)

```bash
$ composer require tbela99/css
```

## Requirements

- PHP version >= 8.0. If you need support for older versions >= 5.6 then checkout [this branch](https://github.com/tbela99/css/tree/php56-backport)
- mbstring extension

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

PHP Code

```php

use \TBela\CSS\Parser;

$parser = new Parser();

$parser->setContent('
h1 {
  color: green;
  color: blue;
  color: black;
}

h1 {
  color: #000;
  color: aliceblue;
}');

echo $parser->parse();
```

Result

```css
h1 {
  color: #f0f8ff;
}
```

Parse the css file and generate the AST

```php

use \TBela\CSS\Parser;
use \TBela\CSS\Renderer;

$parser = new Parser($css);
$element = $parser->parse();

// append an existing css file
$parser->append('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

// append css string
$parser->appendContent($css_string);

// pretty print css
$css = (string) $element;

// minified output
$renderer = new Renderer([
  'compress' => true,
  'convert_color' => 'hex',
  'css_level' => 4,
  'sourcemap' => true,
  'allow_duplicate_declarations' => false
  ]);

// fast
$css = $renderer->renderAst($parser);
// or
$css = $renderer->renderAst($parser->getAst());
// slow
$css = $renderer->render($element);

// generate sourcemap -> css/all.css.map
$renderer->save($element, 'css/all.css');

// save as json
file_put_contents('style.json', json_encode($element));
```

Load the AST and generate css code

```php

use \TBela\CSS\Renderer;
// fastest way to render css
$beautify = (new Renderer())->renderAst($parser->setContent($css)->getAst());
// or
$beautify = (new Renderer())->renderAst($parser->setContent($css));

// or
$css = (new Renderer())->renderAst(json_decode(file_get_contents('style.json')));
```

```php

use \TBela\CSS\Renderer;

$ast = json_decode(file_get_contents('style.json'));

$renderer = new Renderer([
    'convert_color' => true,
    'compress' => true, // minify the output
    'remove_empty_nodes' => true // remove empty css classes
]);

$css = $renderer->renderAst($ast);
```

## Sourcemap generation

```php
$renderer = new Renderer([
  'sourcemap' => true
  ]);

// call save and specify the file name
// generate sourcemap -> css/all.css.map
$renderer->save($element, 'css/all.css');
```

## The CSS Query API

Example: get all background and background-image declarations that contain an image url

```php

$element = Element::fromUrl('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');

foreach ($element->query('[@name=background][@value*="url("]|[@name=background-image][@value*="url("]') as $p) {

    echo "$p\n";
}

```

result

```css
.form-select {
 background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c
/svg%3e")
}
.form-check-input:checked[type=checkbox] {
 background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/s
vg%3e")
}

...
```

Example: Extract Font-src declaration

CSS source

```css
@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body {
  background-color: green;
  color: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
h1 {
  color: #fff;
  font-size: 50px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
}

@media print {
  @font-face {
    font-family: MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
  body {
    font-family: "Bitstream Vera Serif Bold", serif;
  }
  p {
    font-size: 12px;
    color: #000;
    text-align: left;
  }

  @font-face {
    font-family: Arial, MaHelvetica;
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
    font-weight: bold;
  }
}
```

PHP source

```php

use \TBela\CSS\Parser;

$parser = new Parser();

$parser->setContent($css);

$stylesheet = $parser->parse();

// get @font-face nodes by class names
$nodes = $stylesheet->queryByClassNames('@font-face, .foo .bar');

// or

// get all src properties in a @font-face rule
$nodes = $stylesheet->query('@font-face/src');

echo implode("\n", array_map('trim', $nodes));
```

result

```css
@font-face {
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}
@media print {
  @font-face {
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
  }
}
@media print {
  @font-face {
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
  }
}
```

render optimized css

```php

$stylesheet->setChildren(array_map(function ($node) { return $node->copy()->getRoot(); }, $nodes));
$stylesheet->deduplicate();

echo $stylesheet;
```

result

```css
@font-face {
  src: url(/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff)
}
@media print {
 @font-face {
   src: local("Helvetica Neue Bold"), local(HelveticaNeue-Bold), url(MgOpenModernaBold.ttf)
 }
}
```

## CSS Nesting

```css
table.colortable {
  & td {
    text-align:center;
    &.c { text-transform:uppercase }
    &:first-child, &:first-child + td { border:1px solid black }
  }


& th {
text-align:center;
background:black;
color:white;
}
}
```

render CSS nesting

```php

use TBela\CSS\Parser;

echo new Parser($css);

```
result 

```css
table.colortable {
 & td {
  text-align: center;
  &.c {
   text-transform: uppercase
  }
  &:first-child,
  &:first-child+td {
   border: 1px solid #000
  }
 }
 & th {
  text-align: center;
  background: #000;
  color: #fff
 }
}

```

convert nesting CSS to older representation

```php 

use TBela\CSS\Parser;
use \TBela\CSS\Renderer;

$renderer = new Renderer( ['legacy_rendering' => true]);
echo $renderer->renderAst(new Parser($css));

```

result

```css

table.colortable td {
 text-align: center
}
table.colortable td.c {
 text-transform: uppercase
}
table.colortable td:first-child,
table.colortable td:first-child+td {
 border: 1px solid #000
}
table.colortable th {
 text-align: center;
 background: #000;
 color: #fff
}

```

## The Traverser Api

The traverser will iterate over all the nodes and process them with the callbacks provided.
It will return a new tree
Example using ast

```php

use TBela\CSS\Ast\Traverser;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

$parser = (new Parser())->load('ast/media.css');
$traverser = new Traverser();
$renderer = new Renderer(['remove_empty_nodes' => true]);

$ast = $parser->getAst();

// remove @media print
$traverser->on('enter', function ($node) {

    if ($node->type == 'AtRule' && $node->name == 'media' && $node->value == 'print') {

        return Traverser::IGNORE_NODE;
    }
});

$newAst = $traverser->traverse($ast);
echo $renderer->renderAst($newAst);
```

Example using an Element instance

```php

use TBela\CSS\Ast\Traverser;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

$parser = (new Parser())->load('ast/media.css');
$traverser = new Traverser();
$renderer = new Renderer(['remove_empty_nodes' => true]);

$element = $parser->parse();

// remove @media print
$traverser->on('enter', function ($node) {

    if ($node->type == 'AtRule' && $node->name == 'media' && $node->value == 'print') {

        return Traverser::IGNORE_NODE;
    }
});

$newElement = $traverser->traverse($element);
echo $renderer->renderAst($newElement);
```

## Build a CSS Document

```php

use \TBela\CSS\Element\Stylesheet;

$stylesheet = new Stylesheet();

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('background-color', 'white');
$rule->addDeclaration('color', 'black');

echo $stylesheet;

```

output

```css
div {
  background-color: #fff;
  color: #000;
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
    background-color: #fff;
    color: #000;
  }
}
```

```php
$div = $stylesheet->addRule('div');

$div->addDeclaration('max-width', '100%');
$div->addDeclaration('border-width', '0px');

```

output

```css
@media print {
  div {
    background-color: #fff;
    color: #000;
  }
}
div {
  max-width: 100%;
  border-width: 0;
}
```

```php

$media->append($div);

```

output

```css
@media print {
  div {
    background-color: #fff;
    color: #000;
  }
  div {
    max-width: 100%;
    border-width: 0;
  }
}
```

```php

$stylesheet->insert($div, 0);
```

output

```css
div {
  max-width: 100%;
  border-width: 0;
}
@media print {
  div {
    background-color: #fff;
    color: #000;
  }
}
```
Adding existing css
```php

// append css string
$stylesheet->appendCss($css_string);
// append css file
$stylesheet->append('style/main.css');
// append url
$stylesheet->append('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/brands.min.css');


```

## Performance

parsing and rendering ast is 3x faster than parsing an element.

```php

use \TBela\CSS\Element\Parser;
use \TBela\CSS\Element\Renderer;

$parser = new Parser($css);

// parse and render
echo (string) $parser;

// or render minified css
$renderer = new Renderer(['compress' => true]);
echo $renderer->renderAst($parser->getAst());

// slower - will build an Element
echo $renderer->render($parser->parse());
```
## Parser Options

- flatten_import: process @import directive and import the content into the css document. default to false.
- allow_duplicate_rules: allow duplicated rules. By default duplicate rules except @font-face are merged
- allow_duplicate_declarations: allow duplicated declarations in the same rule.
- capture_errors: silently capture parse error if true, otherwise throw a parse exception. Default to true

## Renderer Options

- remove_comments: remove comments.
- preserve_license: preserve comments starting with '/*!'
- compress: minify output, will also remove comments
- remove_empty_nodes: do not render empty css nodes
- compute_shorthand: compute shorthand declaration
- charset: preserve @charset. default to false
- glue: the line separator character. default to '\n'
- indent: character used to pad lines in css, default to a space character
- convert_color: convert colors to a format between _hex_, _hsl_, _rgb_, _hwb_ and _device-cmyk_
- css_level: produce CSS color level 3 or 4. default to _4_
- allow_duplicate_declarations: allow duplicate declarations.
- legacy_rendering: convert nesting css. default false

The full [documentation](https://tbela99.github.io/css) can be found [here](https://tbela99.github.io/css)

---

Thanks to [Jetbrains](https://jetbrains.com) for providing a free PhpStorm license

This was originally a PHP port of https://github.com/reworkcss/css
