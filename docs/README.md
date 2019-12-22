CSS (A CSS parser and minifier written in PHP)

---

![Current version](https://img.shields.io/badge/dynamic/json?label=current%20version&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json) [![Known Vulnerabilities](https://snyk.io/test/github/tbela99/gzip/badge.svg)](https://snyk.io/test/github/tbela99/css)

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

$css = $compiler->compile($ast);

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
