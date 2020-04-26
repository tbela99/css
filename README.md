CSS (A CSS parser and minifier written in PHP)

---

![Current version](https://img.shields.io/badge/dynamic/json?label=current%20version&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json) [![Packagist](https://img.shields.io/packagist/v/tbela99/css.svg)](https://packagist.org/packages/tbela99/css) [![Documentation](https://img.shields.io/badge/dynamic/json?label=documentation&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json)](https://tbela99.github.io/css) [![Known Vulnerabilities](https://snyk.io/test/github/tbela99/gzip/badge.svg)](https://snyk.io/test/github/tbela99/css)

A CSS parser, beautifier and minifier written in PHP. It supports the following features

## Features

- CSS4 colors support
- merge duplicate rules
- remove duplicate declarations
- remove empty rules
- process @import directive
- remove @charset directive
- compute css declarations (margin, padding, border-width, border-radius, font)

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

PHP Code

```php

use \TBela\CSS\Compiler;

$compiler = new Compiler();

$compiler->setContent('
h1 {
  color: green;
  color: blue;
  color: black;
}

h1 {
  color: #000;
  color: aliceblue;
}');

echo $compiler->compile();
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

// pretty print css
$css = (string) $element;

// minified output
$renderer = new Renderer([
  'compress' => true,
  'convert_color' => 'hex',
  'css_level' => 4,
  'allow_duplicate_declarations' => false
  ]);

$css = $renderer->render($element);

// save as json
file_put_contents('style.json', json_encode($element));
```

Load the AST and generate css code

```php

use \TBela\CSS\Compiler;

$ast = json_decode(file_get_contents('style.json'));

$compiler = new Compiler([
    'convert_color' => true,
    'compress' => true, // minify the output
    'remove_empty_nodes' => true // remove empty css classes
]);

$compiler->setData($ast);

$css = $compiler->compile();
```

## CSS manipulation

### Example: Extract Font-src

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

php source

```php

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\Element\AtRule;
use \TBela\CSS\Element\Stylesheet;

$parser = new Parser('', [
    'silent' => false,
    'flatten_import' => true
]);

$parser->load('./css/manipulate.css');

$stylesheet = new Stylesheet();

function getNodes ($data, $stylesheet) {

    $nodes = [];

    foreach ($data as $node) {

        if ($node instanceof AtRule) {

            switch ((string) $node->getName()) {

                case 'font-face':

                    foreach ($node as $declaration) {

                        if ((string) $declaration['name'] == 'src') {

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
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}
@media print {
  @font-face {
    src: local("Helvetica Neue Bold"), local("HelveticaNeue-Bold"),
      url(MgOpenModernaBold.ttf);
  }
}
```

## Build a CSS document

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

## Parser options

- source: CSS source file. It is only used in the exception error message.
- silent: throw an exception if false or silently return an error. default to false
- flatten_import: process @import directive and import the content into the css. default to false.
- allow_duplicate_rules: allow duplicated rules. By default duplicate rules are merged
- allow_duplicate_declarations: allow duplicated declarations in the same rule.

## Compiler options

- charset: if false remove @charset
- glue: the line separator character. default to '\n'
- indent: character used to pad lines in css, default to a space character
- remove_comments: remove comments. If _compress_ is true, comments are always removed
- convert_color: convert colors to a format between _hex_, _hsl_, _rgb_, _hwb_ and _device-cmyk_
- css_level: will use CSS4 or CSS3 color format. default to _4_
- compress: produce minified output
- remove_empty_nodes: remove empty css rules

The full [documentation](https://tbela99.github.io/css) can be found [here](https://tbela99.github.io/css)

## Requirements

PHP version >= 7.1

---

Thanks to [jetbrains](https://jetbrains.com) for providing a free PhpStorm license
