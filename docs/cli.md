
# Command line utility

the command line utility is located at './cli/css-parser'

```bash

$ ./cli/css-parser -h

Usage: 
$ css-parser [OPTIONS] [PARAMETERS]

-h	print help
--help	print extended help

parse options:

-e, --capture-errors                    	ignore parse error

-f, --file                              	css file or url

-m, --flatten-import                    	process @import

-d, --parse-allow-duplicate-declarations	allow duplicate declaration

-p, --parse-allow-duplicate-rules       	allow duplicate rule

render options:

-a, --ast                          	dump ast as JSON

-S, --charset                      	remove @charset

-c, --compress                     	minify output

-u, --compute-shorthand            	compute shorthand properties

-l, --css-level                    	css color module

-G, --legacy-rendering             	legacy rendering

-o, --output                       	output file name

-L, --preserve-license             	preserve license comments

-C, --remove-comments              	remove comments

-E, --remove-empty-nodes           	remove empty nodes

-r, --render-duplicate-declarations	render duplicate declarations

-s, --sourcemap                    	generate sourcemap, require -o

```

## Minify inline css

```bash
$ ./cli/css-parser 'a, div {display:none} b {}' -c
#
$ echo 'a, div {display:none} b {}' | ./cli/css-parser -c
```

### Minify css file

```bash
$ ./cli/css-parser -f nested.css -f 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css' -c
#
$ ./cli/css-parser -f 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/brands.min.css' -c
```

## Dump ast

```bash
$ ./cli/css-parser -f nested.css -c -a
#
$ ./cli/css-parser 'a, div {display:none} b {}' -c -a
#
$ echo 'a, div {display:none} b {}' | ./cli/css-parser -c -a
```
