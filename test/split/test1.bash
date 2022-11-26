#!/bin/bash
TEST_DATA_NUM=20
TEST_DATA=./data/split/test-data${TEST_DATA_NUM}.csv
TEST_EXPECT_DIR=./test/split/expect/test-data${TEST_DATA_NUM}
TEST_RESULT_DIR=./test/split/result/test-data${TEST_DATA_NUM}
START_LINE=1
SPLIT_NUM=10
rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}

php ./split.php ${SPLIT_NUM} ${START_LINE} ${TEST_DATA} ${TEST_RESULT_DIR}

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
