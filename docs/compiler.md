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

String. The string used to indent lines. The default value is ' '

### glue

String. The string used as line separator. The default value is "\n"

### separator

String. The character used to tokens. The default value is ' '

### charset

Boolean. If true then remove @charset declaration

### rgba_hex

Boolean. Convert rgba and hsla to hex

### compress

Boolean. If true then compress the css. If false then pretty print the css

### remove_comments

Boolean. If true then remove comments. When compress is set to true then comments are always removed

### remove_empty_nodes

Boolean. If true then remove empty css rules and media queries

## Compiler Methods
 
### Constructor

Constructor

#### Parameters

- $options: array of options. see [compiler options](#compiler-options)
   
### Compile

Compile AST into css

#### Parameters

none

#### Return type

String
  
### SetOptions

Configure the compiler options. see [compiler options](#compiler-options)

#### Parameters

- $options: array of options
    
#### Return type

\TBela\CSS\Compiler instance
  
### SetContent

#### Parameters

- $css: string. Parse input css
    
#### Return type

\TBela\CSS\Compiler instance
  
### SetData

#### Parameters

- $ast: object. Assign an AST object to the compiler
    
#### Return type

\TBela\CSS\Compiler instance
  