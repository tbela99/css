##!/bin/bash -x
cd $(dirname "$0")
cd ../benchmark

files=$(ls -d ../test/perf_files/*.css)
measure() {
prog="$1"
shift
echo $({ time $($(echo "$prog" | cut -f1 -d ' ') $(echo "$prog" | cut -f2 -s -d ' ') "$@"); } 2>&1 | tail -3 | head -1 | awk '{print $2;}' | sed 's/^0m//')
}
size() {
prog="$1"
shift
# shellcheck disable=SC2046
result=$($(echo "$prog" | cut -f1 -d ' ') $(echo "$prog" | cut -f2 -s -d ' ') "$@" 2>&1)
file_size "${#result}"
}
lpad() {

  x="$1"

  while [ ${#x} -ne "$2" ]
  do
    x="0$x"
  done

  echo "$x"
}
file_size(){

  x="$1"
  u=""

  if [ "$1" -ge 1073741824 ]
    then

    x=$(echo "scale=2; $1 / 1073741824" | bc)
    u="G"

  elif [ "$1" -ge 1048576 ]
    then

    x=$(echo "scale=2; $1 / 1048576" | bc)
    u="M"
  elif [ "$1" -ge 1024 ]
    then

    x=$(echo "scale=2; $1 / 1024" | bc)
    u="Kb"
  fi

  echo $(lpad "$x" 6)"$u"
}
ellipsis() {
  echo $(echo "$@" | awk -v len=13 '{ if (length($0) > len) print substr($0, 1, len-3) "..."; else print; }')
}
execute() {

  result=()

  fn="$1"
  shift
  header="$1"
  shift

  while [ "$#" -gt 0 ]
  do
      i=-1
      for f in $files
      do
          i=$((i + 1))
          [ "${#result}" -lt "$i" ] && result[i]=""

          result[i]+=$("$fn" "$1" "$f")"$pad"
      done
  shift
  done

  echo -e $header
  i=-1
  for f in $files
  do
    i=$((i + 1))
    echo -e $(ellipsis $(basename "$f"))"$pad${result[i]}"
  done
}
benchmark() {

  execute "measure" "$@"
}
getsize() {

  execute "size" "$@"
}

pad="\t\t"
hpad="\t\t"
echo 'Parsing performance'
. ./parser.sh
echo ""
pad="\t\t"
hpad="\t\t"
echo 'Rendering performance (Uncompressed)'
. ./render-uncompressed.sh
echo ""
echo 'Rendering performance (Compressed)'
. ./render-compressed.sh
echo ""
pad="\t\t"
hpad="\t\t"
echo 'Size (Uncompressed)'
. ./uncompressed-size.sh
echo ""
echo 'Size (Compressed)'
. ./compressed-size.sh