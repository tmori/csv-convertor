#!/bin/bash
TEST_ITEM=test-1
CONFIG=./data/validate_data/data/${TEST_ITEM}/validation_spec.json
TEST_EXPECT_DIR=./test/validate_data/expect/${TEST_ITEM}
TEST_RESULT_DIR=./test/validate_data/result/${TEST_ITEM}
START_LINE=1
#rm -rf ${TEST_RESULT_DIR}
#mkdir  ${TEST_RESULT_DIR}


php ./validate_data.php \
    ./test/validate_data/data/${TEST_ITEM}/validation_spec.json \
    ./test/validate_data/data/${TEST_ITEM}/test-data.csv > ${TEST_RESULT_DIR}/result.txt

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
