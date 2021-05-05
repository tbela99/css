##!/bin/bash -x
cd $(dirname "$0")
cd ../benchmark

if [ ! -d ./vendor/sabberworm ]; then

  echo 'Please go to "'$(pwd)'" folder and run `composer install`'
  exit 1
fi

# files
files=$(ls -d ../test/perf_files/*.css)
# files size
sizes=()
# measure test time
measure() {
  prog="$1"
  shift
  { time $($(echo "$prog" | cut -f1 -d ' ') $(echo "$prog" | cut -f2 -s -d ' ') "$@"); } 2>&1 | tail -3 | head -1 | awk '{print $2;}' | sed 's/^0m//'
}
# measure output size
size() {
  prog="$1"
  shift
  # shellcheck disable=SC2046
  result=$($(echo "$prog" | cut -f1 -d ' ') $(echo "$prog" | cut -f2 -s -d ' ') "$@" 2>&1)
  output=$(convert_file_size "${#result}")
  echo $(lpad "$output" 6)" ("$(lpad $(echo "scale=2; ${#result} * 100 / "$(stat -c%s $(echo "$@" | rev | cut -d ' ' -f1 | rev)) | bc) 5)"%)"
}
# left pad with '\ ', will pipe to sed 's/\\ //g' to display the actual space character
lpad() {

  x="$1"
  i="${#x}"

  while [ "$i" -lt "$2" ]; do
    x="\\ $x"
    i=$((i + 1))
  done

  echo "$x"
}
# human readable file size
convert_file_size() {

  x="$1"
  u=""

  if [ "$1" -ge 1073741824 ]; then

    x=$(echo "scale=2; $1 / 1073741824" | bc)
    u="G"

  elif [ "$1" -ge 1048576 ]; then

    x=$(echo "scale=2; $1 / 1048576" | bc)
    u="M"
  elif [ "$1" -ge 1024 ]; then

    x=$(echo "scale=2; $1 / 1024" | bc)
    u="Kb"
  fi

  echo ""$(lpad "$x" 6)"$u"
}
# well, ellipsis
ellipsis() {
  echo "$@" | awk -v len=13 '{ if (length($0) > len) print substr($0, 1, len-3) "..."; else print; }'
}
# generic test runner
# params are
# - test to execute
# - header
# - list of tests
execute() {

  declare -a result=()

  fn="$1"
  shift
  header="$1"
  shift

  while [ "$#" -gt 0 ]; do
    i=-1
    for f in $files; do
      i=$((i + 1))
      [ -z "${#result[@]}" ] && result[i]=""

      result[i]+=$("$fn" "$1" "$f")"$pad"
    done
    shift
  done

  # compute stats
  stats="${fn}_stats"

  if [ "$(LC_ALL=C type -t $stats)" = "function" ]; then

    i=0
    while [ "$i" -lt "${#result[@]}" ]; do

      args=$(echo -e "${result[i]}" | xargs echo)
      result[i]=$($stats "$args")
      i=$((i + 1))
    done
  fi

  echo -e "$header"
  i=-1
  for f in $files; do
    i=$((i + 1))
    echo -e $(ellipsis $(basename "$f"))"$pad"$(convert_file_size "${sizes[i]}")"$pad${result[i]}" | sed 's/\\ / /g'
  done
}
# get file size
file_size() {

  echo ""$(convert_file_size $(stat -c%s "$1"))
}

# benchmark tests
benchmark() {

  execute "measure" "$@"
}
# benchmark test output si<e
getsize() {

  execute "size" "$@"
}
# compute stats
measure_stats() {

  declare -a result=()

  declare min=-1
  declare i=0
  declare v

  for t in $(echo -e "$@"); do

    if [ -z "$t" ]; then
      continue
    fi

    v=$(echo "$t" | cut -d "s" -f1)

    if [ $(echo "$min == -1 " | bc) -eq 1 ] || [ $(echo "$min > $v" | bc) -eq 1 ]; then
      min="$v"
    fi

    result[i]="$t"
    i=$((i + 1))
  done

  i=0

  while [ "$i" -lt "${#result[@]}" ]; do

    result[i]=$(lpad "${result[i]}" 6)" ("$(lpad $(echo "scale=2; ("$(echo "${result[i]}" | cut -d "s" -f1)" - $min) * 100 / $min" | bc) 6)"%)$pad"
    i=$((i + 1))
  done

  echo -e "${result[@]}"
}

i=0
for f in $files; do

  sizes[i]=$(stat -c%s "$f")
  i=$((i + 1))
done

pad="\t\t"
hpad="\t\t"
echo 'Parsing performance'
#. ./parser.sh
#
benchmark "file${hpad}\tsize${hpad}\telement${hpad}\t\tsabber${hpad}\t\tast" "./parse.php" "./parseSabberWorm.php" "./parseast.php"

echo ""
#pad="\t\t"
#hpad="\t\t"
echo 'Rendering performance (Uncompressed)'
#. ./render-uncompressed.sh
#
benchmark "file${hpad}\tsize${hpad}\telement${hpad}\t\tsabber${hpad}\t\tast" "./render.php" "./renderSabberWorm.php" "./renderast.php"

echo ""
echo 'Rendering performance (Compressed)'
#. ./render-compressed.sh
#
benchmark "file${hpad}\tsize${hpad}\telement${hpad}\t\tsabber${hpad}\t\tast" "./render.php -c" "./renderSabberWorm.php -c" "./renderast.php -c"

echo ""
#pad="\t\t"
#hpad="\t\t"
echo 'Size (Uncompressed)'
#. ./uncompressed-size.sh
#
getsize "file\t${hpad}size${hpad}\telement${hpad}\t\tsabber${hpad}\t\tast" "./render.php" "./renderSabberWorm.php" "./renderast.php"

echo ""
echo 'Size (Compressed)'
# . ./compressed-size.sh
#
getsize "file\t${hpad}size${hpad}\telement${hpad}\t\tsabber${hpad}\t\tast" "./render.php -c" "./renderSabberWorm.php -c" "./renderast.php -c"
