# AtRule

This class implements At-Rule logic

Using AST
```php

use \TBela\Element;

$ast = json_decode(file_get_contents('ast.json'));

$stylesheet = Element::getInstance($ast);
```
Using the parser
```php

use \TBela\CSS;

$parser = new Parser($css);

// load css like this
$parser->load('template.css');

// or like that
$parser->seContent($css);

// and then
$stylesheet = $parser->parse();
```

Using the compiler

```php

use \TBela\Compiler;

$compiler = new Compiler();

// load css like this
$compiler->load('template.css');

// or like that
$compiler->setContent($css);

// and then
$stylesheet = $compiler->getData();
```
Building stylesheet manually

```php
$stylesheet = new Stylesheet();

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('background-color', 'white');
$rule->addDeclaration('color', 'black');

$media = $stylesheet->addAtRule('media', 'print');
$media->append($rule);

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('max-width', '100%');
$rule->addDeclaration('border-width', '0px');

$namespace = $stylesheet->addAtRule('namespace', 'svg url(http://www.w3.org/2000/svg)', AtRule::ELEMENT_AT_NO_LIST);

$import = $stylesheet->addAtRule('import', 'url(css/stylesheet.css)', AtRule::ELEMENT_AT_NO_LIST);
$stylesheet->insert($import, 0);

echo $stylesheet;
```
Result

```css
@import url(css/stylesheet.css);
@media print {
 div {
   background-color: #fff;
   color: #000
 }
}
div {
 max-width: 100%;
 border-width: 0
}
@namespace svg url(http://www.w3.org/2000/svg);
```
