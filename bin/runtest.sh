#!/bin/sh
##!/bin/sh -x
# # to run run a particular test, give the file name without extension as a parameter
##  ./runtest.sh Render Ast Sourcemap
# to run all but specific tests, prepend '-' in front of the test name
## ./runtest.sh -Minify -Ast -Sourcemap
# to run all the tests with no argument
## ./runtest.sh
##
#set -x
DIR=$(cd -P -- "$(dirname $(readlink -f "$0"))" && pwd -P)
cd "$DIR"
unset DIR

[ ! -f "../phpunit.phar" ] &&
  wget -O ../phpunit.phar https://phar.phpunit.de/phpunit-9.5.11.phar  &&
  chmod +x ../phpunit.phar
#
#
#../phpunit.phar --bootstrap autoload.php src/*.php
# legacy test display
# for file in $(ls src/*.php); do ../phpunit.phar --bootstrap autoload.php $file; done
# pretty print test -> phpunit 9
fail() {

  echo "test ""$1"" ended with failure" >&2
  exit 1
}

run() {

  #
  php -dmemory_limit=256M ../phpunit.phar -v --colors=always --bootstrap autoload.php --testdox --fail-on-skipped --fail-on-risky --fail-on-incomplete "$@"
}

testName() {

      fname=$(basename "$1" | awk -F . '{print $1}')

      # strip the Test suffix
      echo ""${fname%Test}
}

#
#
cd ../test
pwd
#
#
if [ $# -gt 0 ]; then

  case "$@" in

  *"-"*)
    for file in $(ls src/*.php); do
      fname=$(basename "$file" | awk -F . '{print $1}')

      # strip the Test suffix
      fname=""${fname%Test}

      case "$@" in
      *-$fname*) continue ;;
      *) run "$file" || fail "$file" ;;
      esac
    done
    ;;
  *)
    for file in $(ls src/*.php); do

      fname=$(basename "$file" | awk -F . '{print $1}')
      # strip the Test suffix
      fname=""${fname%Test}

      case "$@" in
      *$fname*)


        run "$file" || fail "$file"
        ;;
      esac
    done
    ;;
  esac
else
    # no argument
    for file in $(ls src/*.php); do
        run "$file" || fail "$file"
    done
fi
