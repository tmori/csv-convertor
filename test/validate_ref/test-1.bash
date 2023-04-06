#!/bin/bash
TEST_ITEM=test-1
CONFIG=./data/validate_ref/data/${TEST_ITEM}/validation_spec.json
TEST_EXPECT_DIR=./test/validate_ref/expect/${TEST_ITEM}
TEST_RESULT_DIR=./test/validate_ref/result/${TEST_ITEM}


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


php ./validate_ref.php \
    ./test/validate_ref/data/${TEST_ITEM}/validation_spec.json \
    ./test/validate_ref/data/${TEST_ITEM}/test-data-id.csv \
    ./test/validate_ref/data/${TEST_ITEM}/test-data.csv \
    > ${TEST_RESULT_DIR}/result.txt

do_check

