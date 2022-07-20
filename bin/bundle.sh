#!/usr/bin/env bash

SRC=$(cd $(dirname "${BASH_SOURCE[0]}") && pwd)
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

rm -rf $APP/builds/ $SRC/codo-$ARCH

docker run --rm -it \
  -u $(id -u):$(id -g) \
  -v $APP:/srv \
  -e "COMPOSER_HOME=/tmp" \
  composer:latest /srv/application app:build codo --build-version="v0.1.0-alpha1"

if [ ! -f "$SRC/$PHPVER/micro-$ARCH.sfx" ]; then
  echo "Could not find the phpmicro build <$SRC/$PHPVER/micro-$ARCH.sfx>"
  exit 1
fi

if [ ! -f "$APP/builds/codo" ]; then
  echo "Could not find the codo build <$APP/builds/codo>"
  exit 1
fi

cat $SRC/$PHPVER/micro-$ARCH.sfx $APP/builds/codo > $SRC/codo-$ARCH

chmod +x $SRC/codo-$ARCH

exit 0
