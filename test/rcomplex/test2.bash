#!/bin/bash
TEST_NO=2
TEST_DATA=./test/rcomplex/data/test${TEST_NO}
TEST_EXPECT_DIR=./test/rcomplex/expect/test${TEST_NO}
TEST_RESULT_DIR=./test/rcomplex/result/test${TEST_NO}
rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}

php ./rcomplex_convert.php ${TEST_DATA}/config.json ${TEST_RESULT_DIR}

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
