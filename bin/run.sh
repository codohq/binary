#!/usr/bin/env bash

SRC=$(cd $(dirname $(realpath "${BASH_SOURCE[0]}")) && pwd)
APP=$(realpath $SRC/..)
KERNEL="$(uname -s)"
MACHINE="$(uname -m)"
PHPVER="php8.1"

case "$KERNEL" in
  Linux)
    ARCH="linux-$MACHINE"
    ;;

  Darwin)
    ARCH="macos-$MACHINE"
    ;;
esac

if [ "$(pgrep tmp-codo)" = "" >/dev/null 2>&1 ]; then
  cat $SRC/$PHPVER/micro-$ARCH.sfx $APP/application > $APP/tmp-codo

  chmod +x $APP/tmp-codo
fi

$APP/tmp-codo "$@"

exit 0
