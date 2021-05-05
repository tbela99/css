# build using phpdocs
# phpdoc still produce incorrect output. a lot of classes are missing.
DIR=$(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd "$DIR/.."
[ -f ./phpDocumentor.phar ] || wget "https://phpdoc.org/phpDocumentor.phar" && chmod +x phpDocumentor.phar
# phpDocumentor does not yet support php8
# you must run this command eventually
# $ sudo apt install php7.4
# install additional tools
# $ sudo apt plantuml grahviz
[ ! -d ./docs/api ] || rm -rf ./docs/api
php7.4 ./phpDocumentor.phar -d ./src/TBela/CSS/ -t ./docs/api
