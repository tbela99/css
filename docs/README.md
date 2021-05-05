CSS (A CSS parser and minifier written in PHP)

---

![Current version](https://img.shields.io/badge/dynamic/json?label=current%20version&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json) [![Packagist](https://img.shields.io/packagist/v/tbela99/css.svg)](https://packagist.org/packages/tbela99/css) [![Documentation](https://img.shields.io/badge/dynamic/json?label=documentation&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json)](https://tbela99.github.io/css) [![Known Vulnerabilities](https://snyk.io/test/github/tbela99/gzip/badge.svg)](https://snyk.io/test/github/tbela99/css)

A CSS parser, beautifier and minifier written in PHP. It supports the following features

## Features

- generate sourcemap
- parse and render CSS
- support CSS4 colors
- merge duplicate rules
- remove duplicate declarations
- remove empty rules
- process @import directive
- remove @charset directive
- compute css shorthand (margin, padding, outline, border-radius, font)
- query the css nodes using xpath like syntax or class name
- transform the css and ast using the traverser api

## Installation

install using [Composer](https://getcomposer.org/)

```bash
$ composer require tbela99/css
```

## Requirements

This library requires PHP version >= 7.4. If you need support for older versions of PHP 5.6 - 7.3 then checkout [this branch](https://github.com/tbela99/css/tree/php56-backport)

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

    if ($node->type == 'AtRule' && $node->name == 'media' && (string) $node->value == 'print') {

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

    if ($node->type == 'AtRule' && $node->name == 'media' && (string) $node->value == 'print') {

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

$stylesheet->appendCss($css_string);

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

// slower
echo $renderer->render($parser->parse());
```
## Parser Options

- flatten_import: process @import directive and import the content into the css. default to false.
- allow_duplicate_rules: allow duplicated rules. By default duplicate rules except @font-face are merged
- allow_duplicate_declarations: allow duplicated declarations in the same rule.
- sourcemap: include source location data

## Renderer Options

- sourcemap: generate sourcemap, default false
- remove_comments: remove comments.
- preserve_license: preserve comments starting with '/*!'
- compress: minify output, will also remove comments
- remove_empty_nodes: do not render empty css nodes
- compute_shorthand: compute shorthand declaration
- charset: preserve @charset
- glue: the line separator character. default to '\n'
- indent: character used to pad lines in css, default to a space character
- convert_color: convert colors to a format between _hex_, _hsl_, _rgb_, _hwb_ and _device-cmyk_
- css_level: produce CSS color level 3 or 4. default to _4_
- allow_duplicate_declarations: allow duplicate declarations.

The full [documentation](https://tbela99.github.io/css) can be found [here](https://tbela99.github.io/css)

---

Thanks to [Jetbrains](https://jetbrains.com) for providing a free PhpStorm license

This was originally a PHP port of https://github.com/reworkcss/css
