#!/bin/bash
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
[ ! -f "../phpunit-5.phar" ] &&
  wget -O ../phpunit-5.phar https://phar.phpunit.de/phpunit-5.phar &&
  chmod +x ../phpunit-5.phar
php56=`which php5.6 2>/dev/null`

if [ ! -f "$php56" ]; then

#  read  -p "php5 is not installed, would you like to install it? [Y/y]" -n1 resp

#  echo -e "\n"

#  case "$resp" in

#  [Yy])
    sudo apt-get install python-software-properties
    sudo add-apt-repository ppa:ondrej/php
    sudo apt-get update
    sudo apt-get install -y php5.6
#    break
#    ;;
#  *)
#    exit
#    ;;
#  esac
fi
cd "$DIR/../test/"
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

flag=$(echo "$1" | cut -c1-1)
if [ "$1" = "" ] || [ "$flag" = "-" ]; then
  skip=""
  [ -n "$1" ] && [ "$flag" = "-" ] && skip=$(echo "$1" | cut -c2-11)
  for file in src/*.php; do
    [ "$file" = "src/$skip.php" ] && continue
    echo "Run test $file"
    $php56 -dmemory_limit=256M ../phpunit-5.phar --bootstrap autoload.php $file || fail "$file"
  done
else

  file="src/$1.php"
  if [ -f "$file" ]; then
    $php56 -dmemory_limit=256M ../phpunit-5.phar --bootstrap autoload.php $file
  else
    echo "Invalid test: $1" && exit 1
  fi
fi
