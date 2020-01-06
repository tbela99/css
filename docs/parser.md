# Parser

Parse CSS and convert it to an AST. An AST is an object representation of the CSS.

## Usage

Load CSS string

```php 

$parser = new \TBela\CSS\Parser();
$parser->setContent('
@import 'css/style.css';
body { border: 0px; }');

$parser->setOptions([  
    'flatten_import' => true,
    'allow_duplicate_declarations' => true,
    'allow_duplicate_rules' => true
]);

echo $compiler->compile();
```

Load CSS file

```php 

$parser = new \TBela\CSS\Parser();
$parser->load($css_file);

$parser->setOptions([  
    'flatten_import' => true,
    'allow_duplicate_declarations' => true,
    'allow_duplicate_rules' => true
]);

echo $compiler->compile();
```

## Parser Options

### flatten_import

_boolean_. Replace the @import directive with actual content

### allow_duplicate_rules

_boolean_. Merge duplicate rules

### allow_duplicate_declarations

_boolean_|_string_|_array_. Default _'background-image'_. Remove duplicate declarations. If you want to preserve multiple declarations for some properties, you can specify them as a string or an array.

```php

// preserve everything
$parser->setOptions(['allow_duplicate_declarations' => false]);

// remove duplicates
$parser->setOptions(['allow_duplicate_declarations' => true]);

// preserve multiple declarations for color 
$parser->setOptions(['allow_duplicate_declarations' => 'color']);

// preserve multiple declarations for color and background-color
$parser->setOptions(['allow_duplicate_declarations' => ['color', 'background-color']);

```

### silent

_boolean_. By default the parser will throw an exception if an invalid css content is provided. 

## Compiler Methods
 
### Constructor

Constructor

#### Parameters

- $css: _string_. css string or file path or url
- $options: _array_. see [parser options](#parser-options)
   
### Parse

Parse CSS and return the CSS stylesheet

#### Parameters

none

#### Return type

\TBela\Element
  
### SetOptions

Configure the parser options. see [compiler options](#compiler-options)

#### Parameters

- $options: _array_. see [parser options](#parser-options)
    
#### Return type

\TBela\CSS\Parser instance
  
### Load

#### Parameters

- $file: _string_. load a css file
    
#### Return type

\TBela\CSS\Parser instance
  
### SetContent

#### Parameters

- $css: _string_.  css string or file path or url
    
#### Return type

\TBela\CSS\Parser instance
  