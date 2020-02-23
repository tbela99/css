#!/bin/sh -x
## you need to install terser
# npm install --save-dev terser
## before you run uglify on WSL, you need to install it there
DIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd "$DIR"
#
#
#../phpunit.phar --bootstrap autoload.php src/*.php
# legacy test display
# for file in src/*.php; do ../phpunit.phar --bootstrap autoload.php $file; done
# pretty print test
for file in src/*.php; do ../phpunit.phar --bootstrap autoload.php --testdox $file; done