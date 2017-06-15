#!/usr/bin/env bash

TEST=$1

while read line
do
    php ./threshold_calc.php -u "$line"
done < "$TEST"

