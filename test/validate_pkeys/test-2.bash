#!/bin/bash
TEST_ITEM=test-2
CONFIG=./data/validate_pkeys/data/${TEST_ITEM}/validation_spec.json
TEST_EXPECT_DIR=./test/validate_pkeys/expect/${TEST_ITEM}
TEST_RESULT_DIR=./test/validate_pkeys/result/${TEST_ITEM}


do_check()
{
    diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
    if [ $? -eq 0 ]
    then
        echo "PASSED: $0"
    else
        echo "FAILED: $0"
    fi
}

rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}


php ./validate_pkeys.php \
    ./test/validate_pkeys/data/${TEST_ITEM}/validation_spec.json \
    ./test/validate_pkeys/data/${TEST_ITEM}/test-data.csv \
    skip_empty \
    > ${TEST_RESULT_DIR}/result.txt

do_check

