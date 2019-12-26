# Compiler

Compile an AST into CSS.

## Usage

Pretty print css 

```php 

$compiler = new \TBela\CSS\Compiler();
$compiler->setContent('body { border: 0px; }');

echo $compiler->compile();
```

Produce minified output

```php 

$compiler->setOptions(['compress' => true, 'rbga_hex' => true, 'charset' => true]);

echo $compiler->compile();
```

Load AST from a json file

```php 

$ast = json_decode(file_get_contents('ast.json'));

$compiler->setOptions(['compress' => true, 'rbga_hex' => true, 'charset' => true]);
$compiler->setData($ast);

echo $compiler->compile();
```

Load a css string

```php 

$ast = json_decode(file_get_contents('style.json'));

$compiler->setOptions(['compress' => true, 'rbga_hex' => true, 'charset' => true]);
$compiler->setContent('body { border: 0px; }');

echo $compiler->compile();
```

## Compiler Options

### indent

_string_. The string used to indent lines. The default value is ' '

### glue

_string_. The string used as line separator. The default value is "\n"

### separator

_string_. The character used to tokens. The default value is ' '

### charset

_boolean_. If false then remove @charset declaration

### rgba_hex

_boolean_. Convert rgba and hsla to hex

### compress

_boolean_. If true then compress the css. If false then pretty print the css

### remove_comments

_boolean_. If true then remove comments. When compress is set to true then comments are always removed

### remove_empty_nodes

_boolean_. If true then remove empty css rules and media queries

## Compiler Methods
 
### Constructor

Constructor

#### Parameters

- $options: _array_. see [compiler options](#compiler-options)
   
### Compile

Compile AST into css

#### Parameters

none

#### Return type

_string_.
  
### SetOptions

Configure the compiler options. see [compiler options](#compiler-options)

#### Parameters

- $options: _array_. see [compiler options](#compiler-options)
    
#### Return type

\TBela\CSS\Compiler instance
  
### SetContent

#### Parameters

- $css: _string_. css string or file or url
    
#### Return type

\TBela\CSS\Compiler instance
  
### SetData

#### Parameters

- $ast: _\TBela\CSS\Element_. Assign an AST object to the compiler
    
#### Return type

\TBela\CSS\Compiler instance
  
### getData

#### Parameters

none
    
#### Return type

\TBela\CSS\Element instance
  