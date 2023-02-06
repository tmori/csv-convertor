#!/bin/bash
TEST_NO=1
TEST_DATA=./test/replace/data/test${TEST_NO}/test-data.csv
TEST_EXPECT_DIR=./test/replace/expect/test${TEST_NO}
TEST_RESULT_DIR=./test/replace/result/test${TEST_NO}
START_LINE=1
rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}

php ./replace.php ${TEST_DATA} col3 eq 'aa1' '' ${START_LINE} ${TEST_RESULT_DIR}

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
