# Element

## Creating an Element instance

An Element instance can be created in multiple ways
From the AST

```php 

use \TBela\Element;

$ast = json_decode(file_get_contents('ast.json'));

$stylesheet = Element::getInstance($ast);
```

Using the parser

```php 

use \TBela\Parser;

$parser = new Parser('template.css');

// or
$parser->load('template.css');

// or
$parser->seContent($css);

$stylesheet = $parser->parse();
```

Using the compiler

```php 

use \TBela\Compiler;

$compiler = new Compiler();

//
$compiler->load('template.css');
// or

$stylesheet = $parser->parse();
```

## Manipulating the AST

The AST is a json representation of the CSS file.
Getting the AST

```php 

$json = json_encode($stylesheet);
$ast = json_decode($json);
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
```


## Iterating over the children


```php

foreach ($element['children'] as $child) {

 // ...
}

// or
foreach ($element as $child) {

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

- $value: _string_

#### Return Type

\TBela\CSS\Element

### GetType

return the node type

#### Arguments

none

#### Return Type

_string_
