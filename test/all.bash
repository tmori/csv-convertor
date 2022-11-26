#!/bin/bash

for dir in `ls ./test`
do
    if [ -d ./test/$dir ]
    then
        for test_script in `ls ./test/$dir/*.bash`
        do
            bash $test_script
        done        
    fi
done