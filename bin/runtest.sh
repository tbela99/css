#!/bin/sh
##!/bin/sh -x
# # to run run a particular test, give the file name without extension as a parameter
##  ./runtest.sh Render Ast Sourcemap
# to run all specific tests, prepend '-' in front of the test name to exclude
## ./runtest.sh -Minify -Ast -Sourcemap
# to run all the tests with no argument
## ./runtest.sh
##
#set -x
DIR=$(cd -P -- "$(dirname $(readlink -f "$0"))" && pwd -P)
cd "$DIR"

if [ ! -f "../vendor/bin/phpunit" ]; then
  echo "please go to "$(dirname "$DIR")" and run 'composer install'"
  exit 1
fi

unset DIR
TEST_PCNTL=$(php -m | grep pcntl)
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
  #  set -x
  php ../vendor/bin/phpunit -v --colors=always --bootstrap autoload.php --testdox --fail-on-skipped --fail-on-risky --fail-on-incomplete "$@"

  if [ -n "$TEST_PCNTL" ]; then

    PROCESS_ENGINE=process php ../vendor/bin/phpunit -v --colors=always --bootstrap autoload.php --testdox --fail-on-skipped --fail-on-risky --fail-on-incomplete "$@"
    # unset $PROCESS_ENGINE
  fi
  #  set +x
}

testName() {

  fname=$(basename "$1" | awk -F . '{print $1}')

  # strip the Test suffix
  echo ""${fname%Test}
}

#
#
cd ../test
#pwd
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
