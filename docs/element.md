# Element

## Creating an Element instance

An Element instance can be created in multiple ways
From the AST

```php

use \TBela\CSS\Element;

$ast = json_decode(file_get_contents('ast.json'));

$stylesheet = Element::getInstance($ast);
```

Using the parser

```php

use \TBela\CSS\Parser;

$parser = new Parser($css);

// or
$parser->load('template.css');

// or
$parser->seContent($css);

// and then
$stylesheet = $parser->parse();
```

Using the compiler

```php

use \TBela\CSS\Compiler;

$compiler = new Compiler();

//
$compiler->load('template.css');

// or
$compiler->setContent($css);

// and then
$stylesheet = $compiler->getData();
```

## Manipulating the AST

The AST is a json representation of the CSS file.
Getting the AST

```php

$json = json_encode($stylesheet);
$ast = json_decode($json);

$compiler->setData($ast);


// print css ...
echo $compiler->compile();
```

## Properties

### childNodes

If the element can contain children, they can be accessed using the syntax \$element['childNodes']

```php
$childNodes = $element['childNodes'];

// or
$childNodes = $element->getChildren();

// or
$childNodes = $element['children'];
```

### firstChild

Return the first child element

```php
$firstChild = $element['firstChild'];
```

### lastChild

Return the last child element

```php
$lastChild = $element['lastChild'];
```

## Methods shortcut

Setters and getters methods can be accessed using array-like notation

```php

$type = $element['type'];
// or
$type = $element->getType();


$children = $element['children'];
// or
$children = $element->getChildren();

$name = $element['name'];
// or
$name = $element->getName();

$element['value'] = 'bold';
// or
$element->setValue('bold');

$element['name'] = 'src';
// or
$element->setName('src');
```

## Iterating over the children

```php

foreach ($element as $child) {

 // ...
}

// or
foreach ($element['childNodes'] as $child) {

 // ...
}

// or
foreach ($element->getChildren() as $child) {

 // ...
}

// or
foreach ($element['children'] as $child) {

 // ...
}
```

## Methods

### GetRoot

Return the stylesheet root element

#### Arguments

none

#### Return Type

\TBela\CSS\Element

### GetParent

Return the parent element

#### Arguments

none

#### Return Type

\TBela\CSS\Element

### Copy

Clone the element and its parents. returns the copy of the root element

#### Arguments

none

#### Return Type

\TBela\CSS\Element

### GetValue

Return the value

#### Arguments

none

#### Return Type

_string_

### SetValue

Set the value

#### Arguments

- \$value: _string_

#### Return Type

\TBela\CSS\Element

### GetType

return the node type

#### Arguments

none

#### Return Type

_string_
