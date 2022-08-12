#!/bin/sh
# # to run run a particular test, give the file name without extension as a parameter
##  ./runtest.sh Render Ast Sourcemap
# to run all specific tests, prepend '-' in front of the test name to exclude
## ./runtest.sh -Minify -Ast -Sourcemap
# to run all the tests with no argument
## ./runtest.sh
#set -x
##
DIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd "$DIR/../test/"

php56=`command -v php5.6 2>/dev/null`

if [ -z "$php56" ]; then

  echo "php5 is not installed, installing php5"

#  echo -e "\n"

#  case "$resp" in

#  [Yy])
  which_apt=$(command -v apt 2>/dev/null)

  if [ ! -z "$which_apt" ]; then
    sudo apt-get install -y python-software-properties
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get update -y
    sudo apt-get install -y php5.6

  fi

php56=`command -v php5.6 2>/dev/null`

if [ -z "$php56" ]; then

  echo 'could not find php5 executable, please install it and try again'
  exit 1
fi

#    break
#    ;;
#  *)
#    exit
#    ;;
#  esac
fi
if [ ! -f "../vendor/bin/phpunit" ]; then
  echo "please go to "$(dirname "$DIR")" and run 'composer install'"
  exit 1
fi

unset DIR
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
  $php56 -dmemory_limit=512M ../vendor/bin/phpunit -v --enforce-time-limit --colors=always --bootstrap autoload.php --testdox --fail-on-risky "$@"
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
