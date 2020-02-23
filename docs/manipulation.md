
## Example build a css document

```php

use \TBela\CSS\Element\Stylesheet;

$stylesheet = new Stylesheet();

$rule = $stylesheet->addRule('div');

$rule->addDeclaration('background-color', 'white');
$rule->addDeclaration('color', 'black');

echo $stylesheet;

```
output
```css
div {
 background-color: white;
 color: black
}
```
```php

$media = $stylesheet->addAtRule('media', 'print');
$media->append($rule);

```
output
```css
@media print {
  div {
   background-color: white;
   color: black
 }
}
```

```php
$rule = $stylesheet->addRule('div');

$rule->addDeclaration('max-width', '100%');
$rule->addDeclaration('border-width', '0px');

```
output
```css
@media print {
  div {
   background-color: white;
   color: black
 }
}
div {
 max-width: 100%;
 border-width: 0px
}
```

```php

$media->append($rule);

```
output
```css
@media print {
  div {
   background-color: white;
   color: black
 }
  div {
   max-width: 100%;
   border-width: 0px
 }
}
```

```php

$stylesheet->insert($rule, 0);
```
output
```css
div {
 max-width: 100%;
 border-width: 0px
}
@media print {
  div {
   background-color: white;
   color: black
 }
}
```

## Extract Font-src from a document

CSS source
```css
@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body{
background-color: green;
color: #fff;
font-family: Arial, Helvetica, sans-serif;
}
h1{
color: #fff;
font-size: 50px;
font-family: Arial, Helvetica, sans-serif;
font-weight: bold;
}

@media print {
	@font-face {
  font-family: MaHelvetica;
  src: local("Helvetica Neue Bold"),
       local("HelveticaNeue-Bold"),
       url(MgOpenModernaBold.ttf);
  font-weight: bold;
}
body {
  font-family: "Bitstream Vera Serif Bold", serif;
}
p{
font-size: 12px;
color: #000;
text-align: left
}

    @font-face {
        font-family: Arial, MaHelvetica;
        src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
        font-weight: bold;
    }
	}
```

php source
```php 

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\Element\AtRule;
use \TBela\CSS\Element\Stylesheet;

$parser = new Parser();

$parser->setOptions([
                        'silent' => false,
                        'flatten_import' => true
                    ]);
$parser->load('./css/manipulate.css');

$stylesheet = new Stylesheet();

function getNodes ($data, $stylesheet) {

    $nodes = [];

    foreach ($data as $node) {

        if ($node instanceof AtRule) {

            switch ($node->getName()) {

                case 'font-face':

                    foreach ($node as $declaration) {

                        if ($declaration['name'] == 'src') {

                            $stylesheet->append($declaration->copy()->getRoot());
                            break;
                        }
                    }

                    break;

                case 'media':

                    getNodes($node, $stylesheet);
                    break;
            }
        }
    }
}

getNodes ($parser->parse(), $stylesheet);

// deduplicate rules
$stylesheet = Element::getInstance($parser->deduplicate($stylesheet));

echo $stylesheet;
```

result

```css 
@font-face {
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff")
}
@media print {
 @font-face {
   src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf)
 }
}
```


## Extract @Font-face rules from a document

CSS source
```css
@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff");
}

body{
background-color: green;
color: #fff;
font-family: Arial, Helvetica, sans-serif;
}
h1{
color: #fff;
font-size: 50px;
font-family: Arial, Helvetica, sans-serif;
font-weight: bold;
}

@media print {
	@font-face {
  font-family: MaHelvetica;
  src: local("Helvetica Neue Bold"),
       local("HelveticaNeue-Bold"),
       url(MgOpenModernaBold.ttf);
  font-weight: bold;
}
body {
  font-family: "Bitstream Vera Serif Bold", serif;
}
p{
font-size: 12px;
color: #000;
text-align: left
}

    @font-face {
        font-family: Arial, MaHelvetica;
        src: local("Helvetica Neue Bold"),
        local("HelveticaNeue-Bold"),
        url(MgOpenModernaBold.ttf);
        font-weight: bold;
    }
	}
```

php source
```php 

use \TBela\CSS\Parser;
use \TBela\CSS\Element;
use \TBela\CSS\Element\AtRule;
use \TBela\CSS\Element\Stylesheet;

$parser = new Parser();

$parser->setOptions([
                        'silent' => false,
                        'flatten_import' => true
                    ]);
$parser->load('./css/manipulate.css');

$stylesheet = new Stylesheet();

function getNodes ($data, $stylesheet) {

    $stack = [$data];

    while ($current = array_shift($stack)) {

        foreach ($current as $node) {

            if ($node instanceof AtRule) {

                switch ($node->getName()) {

                    case 'font-face':

                        $stylesheet->append($node->copy()->getRoot());
                        break;

                    case 'media':

                        getNodes($node, $stylesheet);
                        break;
                }
            }
        }
    }
}

getNodes ($parser->parse(), $stylesheet);

// deduplicate rules
$stylesheet = Element::getInstance($parser->deduplicate($stylesheet));

echo $stylesheet;
```

result

```css 
@font-face {
  font-family: "Bitstream Vera Serif Bold";
  src: url("/static/styles/libs/font-awesome/fonts/fontawesome-webfont.fdf491ce5ff5.woff")
}
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
