#!/usr/bin/env bash

TEST=$1
PHPFILE=$2

while read line
do
    php $PHPFILE -u "$line"
done < "$TEST"

