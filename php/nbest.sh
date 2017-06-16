#!/usr/bin/env bash

TEST=$1

while read line
do
    if [ $( echo $line | wc -w ) -lt 9 ] ; then
        #echo "Less than 9"
        php ./nbest_calc.php -u "$line"
        #echo "More than 9"
    fi
done < "$TEST"

