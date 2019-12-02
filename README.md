CSS (A CSS parser and minifier written in PHP)
____

![Current version](https://img.shields.io/badge/dynamic/json?label=current%20version&query=version&url=https%3A%2F%2Fraw.githubusercontent.com%2Ftbela99%2Fcss%2Fmaster%2Fcomposer.json) [![Known Vulnerabilities](https://snyk.io/test/github/tbela99/gzip/badge.svg)](https://snyk.io/test/github/tbela99/css) 

A CSS parser and minifier written in PHP.

This is a PHP port of https://github.com/reworkcss/css with following changes:

- it does not follow the original api
- it can process @import directive. This improves performance as the number of http requests is reduced
- it does remove @charset directive
- source map support has been removed. This produce smaller AST. During my test with a 150Kb css file, the AST with source map was 450Mb while without AST it was only 300Kb. Maybe this was an issue with my implementation but I have no time to verify that hypothesis

### Usage:

```css
@import "import-media.css" print;
@import url('./css/url-import-media.css') all;
@import "import.css";
@import url('css/url-import.css');

@charset 'utf-8';

@keyframes slidein {
	from {
		transform: translateX(0%);
	}

	to {
		transform: translateX(100%);
	}
}

@keyframes identifier {
	0% {
		top: 0;
		left: 0;
	}

	30% {
		top: 50px;
	}

	68%,
	72% {
		left: 50px;
	}

	100% {
		top: 100px;
		left: 100%;
	}
}

/* removed empty rules */
@keyframes identifier2 {
	0% {}

	30% {}

	68%,
	72% {}

	100% {}
}

/* removed empty rules */
.removable {}

/* removed empty @media */

@media all {

	.removable {}


	@media none {

		.removable {}
	}

}
```

Parse the css file and generate the AST

```php

$parser = new \CSS\Parser([
    'flatten_import' => true, // process @import directive
    'silent' => false // throw exception on error
]);
$ast = $parser->parse(file_get_contents('style.css'));

file_put_contents('style.json', json_encode($ast));
```

Load the AST and generate css code 
```php

$ast = json_decode(file_get_contents('style.json'));

$compiler = new \CSS\Compiler([
    'indent' => ' ', // character used to indent file
    'compress' => false, // minify the output
    'remove_empty_nodes' => true // remove empty css classes
]);

$parser->compile($ast);

```

css output

```css
/* @imported import-media.css */
@media print {
.imported-from-media {
outline: 0;
color: #a1a1a1a1;
}

}
/* @imported ./css/url-import-media.css */
@media all {
.url-imported-from-media {
outline: 0;
color: #a1a1a1a1;
background-image: url(img/d4ca075023f827d81478565989.png);
}

}
/* @imported import.css */
.imported {
outline: 0;
color: #a1a1a1a1;
}

/* @imported css/url-import.css */
@media print {
.url-imported {
outline: 0;
color: #a1a1a1a1;
background: url(img/d4ca075023f827d81478565989.png);
}

}

@keyframes slidein {
from {
transform: translateX(0%);
}

to {
transform: translateX(100%);
}

}
@keyframes identifier {
0% {
top: 0;
left: 0;
}

30% {
top: 50px;
}

68%, 72% {
left: 50px;
}

100% {
top: 100px;
left: 100%;
}

}
/* removed empty rules */

/* removed empty rules */

/* removed empty @media */

```


## Parser options

- source: CSS source file. It is only used in the exception error message.
- silent: throw an exception if false or silenty return an error. default to false
- flatten_import: process @import directive and import the content into the css. default to false.

## Compiler options

- indent: character used to pad lines in css, default to a space character
- compress: produce minified output
- remove_empty_nodes: remove empty css declaration

## TODO 

- provide an interface to query css through the AST
- improve css minification