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
    'deduplicate_declarations' => true,
    'deduplicate_rules' => true
]);

echo $compiler->compile();
```

Load CSS file

```php 

$parser = new \TBela\CSS\Parser();
$parser->load($css_file);

$parser->setOptions([  
    'flatten_import' => true,
    'deduplicate_declarations' => true,
    'deduplicate_rules' => true
]);

echo $compiler->compile();
```

## Parser Options

### flatten_import

Boolean. Replace the @import directive with actual content

### deduplicate_rules

Boolean. Merge duplicate rules

### deduplicate_declarations

Boolean. Remove duplicate declarations

### silent

Boolean. By default the parser will throw an exception if an invalid css content is provided. 

## Compiler Methods
 
### Constructor

Constructor

#### Parameters

- $options: array of options. see [parser options](#parser-options)
   
### Parse

Parse CSS and return the AST

#### Parameters

none

#### Return type

Object
  
### SetOptions

Configure the parser options. see [compiler options](#compiler-options)

#### Parameters

- $options: array of options
    
#### Return type

\TBela\CSS\Parser instance
  
### Load

#### Parameters

- $file: string. load a css file
    
#### Return type

\TBela\CSS\Parser instance
  
### SetContent

#### Parameters

- $css: string. Parse input css
    
#### Return type

\TBela\CSS\Parser instance
  