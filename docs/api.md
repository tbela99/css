
## CSS Api

## Rendering an Element

Every Element instance implement a \_\_toString() method which means they are automatically converted to string where a string is expected.
However you can control how the element is rendered by using a _Renderer_. 
The renderer has a _setOptions_ method that accepts the same arguments as [\TBela\CSS\Compiler::setOptions](./compiler.md#compiler-options)
Two renderer classes are provided.
The pretty print rendering is done using the class _\TBela\CSS\Renderer_ while minified printing is done using _\TBela\CSS\Compress_

### Pretty printing CSS

Elements are rendered by default using Pretty printing. 

Example 

```css

@media print {
 @font-face {
   font-family: Arial, MaHelvetica;
   src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
   font-weight: bold
 }
}

```

```php

use \TBela\CSS\Parser;
use \TBela\CSS\Renderer;

$parser = new Parser();
$renderer = new Renderer();

$parser->setContent($css);

$stylesheet = $parser->parse();

// get @font-face element
$media = $stylesheet['children'][0];
$fontFace = $media['children'][0];


```
Render the element alone
```php
echo $renderer->render($fontFace);
```
css output
```css
@font-face {
  font-family: Arial, MaHelvetica;
  src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
  font-weight: bold
}
```

render the element with its parents
```php
echo $renderer->render($fontFace, null, true);
```
Css output
```css
   @media print {
       @font-face {
           font-family: Arial, MaHelvetica;
      src: local("Helvetica Neue Bold"),
           local("HelveticaNeue-Bold"),
           url(MgOpenModernaBold.ttf);
      font-weight: bold
    }
   }
```

### Minify CSS printing

There are additional settings to control minification.
- _compress_: true/false. Enable minification
- _rgba_hex_: true/false. Convert rgba and hsla colors to hex


Example 

```css

@media print {
 @font-face {
   font-family: Arial, MaHelvetica;
   src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
   font-weight: bold
 }
}

```

```php

use \TBela\CSS\Parser;
use \TBela\CSS\Renderer;

$parser = new Parser();
$compressor = new Renderer();

// convert rgba to hex, not required here
$compressor->setOptions(['rgba_hex' => true, 'compress' => true]);

$parser->setContent($css);

$stylesheet = $parser->parse();

// get @font-face element
$media = $stylesheet['children'][0];
$fontFace = $media['children'][0];


```
Render the element alone
```php
echo $compressor->render($fontFace);
```
css output
```css
@font-face{font-family:Arial,MaHelvetica;src:local("Helvetica Neue Bold"),local("HelveticaNeue-Bold"),url(MgOpenModernaBold.ttf);font-weight:bold}
```

render the element with its parents
```php
echo $compressor->render($fontFace, null, true);
```
Css output
```css
@media print{@font-face{font-family:Arial,MaHelvetica;src:local("Helvetica Neue Bold"),local("HelveticaNeue-Bold"),url(MgOpenModernaBold.ttf);font-weight:bold}}
```
