## Query Api

It allows you to query the CSS node in using and xpath like language. 

### Node Selectors

the node selectors are

- '*': match all nodes
- '.' : match the current node
- '..': match the parent node
- '/': match the root nodes
- '//' : match all descendant nodes
- 'a': match all nodes with a selector or name 'a'
- 'a[2]': match the second node with name or selector equals to 'a'
- 'a,b': match nodes with name or selector that is either 'a' or 'b'
- '@media[@value=print]': match all media with value equals to 'print'
- '@media[@value^=print]': match all media with value that begins with 'print'
- '@media[@value*=print]': match all media with value that contains 'print'
- '@media[contains(@value, 'print')]': match all media with value that contains 'print'
- '@media[@value*=print]': match all media with value that ends with 'print'

```php
# get all @font-face in the stylesheet
$nodes = $element->query('@font-face');
```

### Attributes Filters

Nodes can be filtered using attributes. An attributes is contained inside \[ and \]. An attribute name starts with '@'.
Attributes are attribute name (which are @name and @value) or function filter

- @name: this attribute designates either the node selector, the @atRule name or the css declaration name
Example
```php
// match all nodes with a name
$nodes = $element->query('[@name]');
// match all @media
$nodes = $element->query('[@name="media"]');
```
- @value: this attribute designates the css declaration value or @AtRule attributes

```php
// match all nodes with a value
$nodes = $element->query('[@value]');
// match all nodes with value print like @media print {}
$nodes = $element->query('[@value="print"]');
```
### Operators

Operators are used to test attributes. They can only be used inside \[ and \]

- equals (=)

```php
$nodes = $element->query('[@value="url(./images/flower.jpg)"]');
```
- begins with (^=)

```php
$nodes = $element->query('[@value^="print"]');
```
- ends with ($=)

```php
$nodes = $element->query('[@value$="print"]');
```
- contains (*=)

```php
$nodes = $element->query('[@value*="print"]');
```

### Function Filters

Nodes can be filtered using functions. Functions are 

- color(@attr, 'value'): match all nodes with attributes that match the specified color
```php
// match all declarations with value that match white color
$nodes = $element->query('[color(@value, "white")]');
```
- contains(@attr, 'value'): match all nodes with attributes that contains the specified value
```php
// match all nodes with value that contains print
$nodes = $element->query('[contains(@attr, "print")]');
```
- beginswith(@attr, 'value'): match all nodes with attributes that begin with the specified value
```php
// match all nodes with value that contains print
$nodes = $element->query('[beginswith(@attr, "print")]');
```
- endswith(@attr, 'value'): match all nodes with attributes that end with the specified value
```php
// match all nodes with value that contains print
$nodes = $element->query('[endswith(@attr, "print")]');
```
- equals(@attr, 'value'): match all nodes with attributes that are equal to specified value
```php
// match all nodes with value that contains print
$nodes = $element->query('[equals(@attr, "print")]');
```
- empty(): match empty rules
```php
// match all empty nodes
$nodes = $element->query('[empty()]');
```
- comment(): match comments node
```php
// match all comment nodes
$nodes = $element->query('[comment()]');
```