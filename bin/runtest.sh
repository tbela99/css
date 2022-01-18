#!/bin/sh
##!/bin/sh -x
# # to run run a particular test, give the file name without extension as a parameter
##  ./runtest.sh Render
# to run all tests but a specific test, prepend '-' in front of the test name
## ./runtest.sh -Minify
# to run all the tests with no argument
## ./runtest.sh
##
DIR=$(cd -P -- "$(dirname $(readlink -f "$0"))" && pwd -P)

[ ! -f "../phpunit.phar" ] &&
  wget -O ../phpunit.phar https://phar.phpunit.de/phpunit.phar &&
  chmod +x ../phpunit.phar
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

if [ $# -gt 0 ]; then

  case "$@" in

  *"-"*)
    for file in src/*.php; do
      fname=$(basename "$file" | awk -F . '{print $1}')

      case "$@" in
      *-$fname*) continue ;;
      *) php -dmemory_limit=256M ../phpunit.phar --colors=always --bootstrap autoload.php --testdox "$file" || fail "$file" ;;
      esac
    done
    ;;
  *)
    for file in src/*.php; do

      fname=$(basename "$file" | awk -F . '{print $1}')

      case "$@" in
      *$fname*)
        php -dmemory_limit=256M ../phpunit.phar --colors=always --bootstrap autoload.php --testdox "$file" || fail "$file"
        ;;
      esac
    done
    ;;
  esac
else
    # no argument
    for file in src/*.php; do
        php -dmemory_limit=256M ../phpunit.phar --colors=always --bootstrap autoload.php --testdox "$file" || fail "$file"
    done
fi
