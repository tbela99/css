# Traverser

Transform Element instance or ast into a new instance.

## Events

- enter: triggered when entering a node
- exit: triggered when exiting a node

## Methods

### On

add a callback for the specified event

#### Arguments

- event: _string_ event name
- callable: _callable_ the callback

#### Usage

```php

use TBela\CSS\Ast\Traverser;

$traverser = new Traverser();
$callable = function ($node) {

    // remove @media print {}
    if ($node->type == 'AtRule' && $node->value == 'print') {
    
        return Traverser::IGNORE_NODE;
    }
};

$traverser->on('enter', $callable);
//$traverser->on('exit', $callable);

```

### Off

remove a callback for the specified event

#### Arguments

- event: _string|null_ event name. if event is null, all callbacks from all events are removed
- callable: _callable|null_ remove the specified callback from the event listeners. if callable is null, all event listeners for the specified event are removed.

#### Usage

```php

use TBela\CSS\Ast\Traverser;

$traverser = new Traverser();
$callable = function ($node) {

    // remove @media print {}
    if ($node->type == 'AtRule' && $node->value == 'print') {
    
        return Traverser::IGNORE_NODE;
    }
};

$traverser->on('enter', $callable);
// ...

// remove callback
$traverser->off('enter', $callable);
```

## Element Traverse Api

```php

use TBela\CSS\Parser;

$parser = (new Parser($css, $options))->parse();
$element = $parser->parse();

// traverse the element tree and return a new tree
$other = $element->traverse('enter', function ($node) {
    // do something
}

```
