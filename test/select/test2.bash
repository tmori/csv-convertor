#!/bin/bash
TEST_NO=2
TEST_DATA=./test/select/data/test${TEST_NO}/test-data.csv
TEST_EXPECT_DIR=./test/select/expect/test${TEST_NO}
TEST_RESULT_DIR=./test/select/result/test${TEST_NO}
START_LINE=1
rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}

php ./select.php ${TEST_DATA} col1 ne 2 ${START_LINE} ${TEST_RESULT_DIR}

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
