#!/bin/sh
##!/bin/sh -x
# # to run run a particular test, give the file name without extension as a parameter
##  ./runtest.sh Render
##
DIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd "$DIR"
#
#
#../phpunit.phar --bootstrap autoload.php src/*.php
# legacy test display
# for file in src/*.php; do ../phpunit.phar --bootstrap autoload.php $file; done
# pretty print test -> phpunit 9
if [ "$1" == "" ]; then
  for file in src/*.php
    do
      php -dmemory_limit=256M ../phpunit.phar --bootstrap autoload.php --testdox $file || $(echo "test ""$file"" ended with failure" >&2 && exit 1)
    done
else

  file="src/$1.php"
  if [ -f "$file" ]; then
    php -dmemory_limit=256M ../phpunit.phar --bootstrap autoload.php --testdox $file
  else
    echo "Invalid test: $1"
  fi
fi
