# build using doxygen
# phpdoc still has few issues that prevent using him
DIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd "$DIR/.."
[ ! -d ./docs/api ] || rm -rf ./docs/api
doxygen