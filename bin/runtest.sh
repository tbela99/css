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
cd "$DIR/../test/"
[ ! -f "../phpunit-5.phar" ] &&
  wget -O ../phpunit-5.phar https://phar.phpunit.de/phpunit-5.phar &&
  chmod +x ../phpunit-5.phar
php56=`command -v php5.6 2>/dev/null`


if [ ! -f "$php56" ]; then

  echo "php5 is not installed, installing php5"

#  echo -e "\n"

#  case "$resp" in

#  [Yy])
    sudo apt-get install python-software-properties
    sudo add-apt-repository ppa:ondrej/php
    sudo apt-get update
    sudo apt-get install -y php5.6


php56=`command -v php5.6 2>/dev/null`
#    break
#    ;;
#  *)
#    exit
#    ;;
#  esac
fi
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
      fname=$(echo -e "$file" | cut -c 5- | awk -F . '{print $1}')

      case "$@" in
      *-$fname*) continue ;;
      *) $php56 -dmemory_limit=256M ../phpunit-5.phar --colors=always --bootstrap autoload.php --testdox "$file" || fail "$file" ;;
      esac
    done
    ;;
  *)
    for file in src/*.php; do
      fname=$(echo -e "$file" | cut -c 5- | awk -F . '{print $1}')

      case "$@" in
      *$fname*)
        $php56 -dmemory_limit=256M ../phpunit-5.phar --colors=always --bootstrap autoload.php --testdox "$file" || fail "$file"
        ;;
      esac
    done
    ;;
  esac
else
    # no argument
    for file in src/*.php; do
        $php56 -dmemory_limit=256M ../phpunit-5.phar --colors=always --bootstrap autoload.php --testdox "$file" || fail "$file"
    done
fi
