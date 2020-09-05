#!/bin/sh
##!/bin/sh -x
# # to run run a particular test, give the file name without extension as a parameter
##  ./runtest.sh Render
# to run all tests but a specific test, prepend '-' in front of the test name
## ./runtest.sh -Minify
# to run all the tests with no argument
## ./runtest.sh
##
DIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd "$DIR"
[ ! -f "../phpunit-5.phar" ] && \
wget -O ../phpunit-5.phar https://phar.phpunit.de/phpunit-5.phar && \
chmod +x ../phpunit-5.phar
whichphp() {

        if [ -f '/bin/php56' ]; then
                echo '/bin/php56'
        elif [ -f '/bin/php5.6' ]; then
                echo '/bin/php5.6'
        elif [ -f '/bin/php' ]; then
                echo '/bin/php'
        fi
}
php56=whichphp
#
#
#../phpunit.phar --bootstrap autoload.php src/*.php
# legacy test display
# for file in src/*.php; do ../phpunit.phar --bootstrap autoload.php $file; done
# pretty print test -> phpunit 9
fail() {

  echo "test ""$1"" ended with failure" >&2
  exit 1
}

if [ "$1" = "" ] || [ "${1:0:1}" = "-" ]; then
  skip=""
  [ -n "$1" ] && [ "${1:0:1}" = "-" ] && skip="${1:1:10}"
  for file in src/*.php
    do
      [ "$file" = "src/$skip.php" ] && continue;
      echo "Run test $file"
      php56 -dmemory_limit=256M ../phpunit-5.phar --bootstrap autoload.php $file || fail "$file"
    done
else

    file="src/$1.php"
    if [ -f "$file" ]; then
      php56 -dmemory_limit=256M ../phpunit-5.phar --bootstrap autoload.php $file
    else
      echo "Invalid test: $1" && exit 1
    fi
fi