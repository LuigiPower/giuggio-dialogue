#!/usr/bin/env bash

TEST=$1
PHPFILE=$2

while read line
do
    if [ $( echo $line | wc -w ) -lt 9 ] ; then
        #echo "Less than 9"
        php $PHPFILE -u "$line"
        #echo "More than 9"
    fi
    #php $PHPFILE -u "$line"
done < "$TEST"

