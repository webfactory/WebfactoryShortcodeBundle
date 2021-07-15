#!/bin/sh

sed --in-place --regexp-extended --expression='s/"(symfony\/.*)": "\^.*"/"\1": "'$VERSION'"/' composer.json
