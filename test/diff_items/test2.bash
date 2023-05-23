#!/bin/bash
TEST_NO=2
TEST_DATA=./test/diff_items/data/test${TEST_NO}
TEST_EXPECT_DIR=./test/diff_items/expect/test${TEST_NO}
TEST_RESULT_DIR=./test/diff_items/result/test${TEST_NO}
rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}

php ./diff_items.php ${TEST_DATA}/config.json > ${TEST_RESULT_DIR}/diff.txt

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
