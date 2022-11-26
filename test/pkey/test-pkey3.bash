#!/bin/bash
PKEY_NUM=3
CONFIG=./data/pkey/data/pkey${PKEY_NUM}/pkey.json
TEST_EXPECT_DIR=./test/pkey/expect/pkey${PKEY_NUM}
TEST_RESULT_DIR=./test/pkey/result/pkey${PKEY_NUM}
START_LINE=1
rm -rf ${TEST_RESULT_DIR}
mkdir  ${TEST_RESULT_DIR}


php ./cp_by_pkey.php \
    ./test/pkey/data/pkey${PKEY_NUM}/pkey.json \
    ./test/pkey/data/pkey${PKEY_NUM}/test-data-parent.csv \
    ./test/pkey/data/pkey${PKEY_NUM}/test-data-child.csv \
    ${TEST_RESULT_DIR}/result.csv

diff -r ${TEST_EXPECT_DIR} ${TEST_RESULT_DIR}
if [ $? -eq 0 ]
then
    echo "PASSED: $0"
else
    echo "FAILED: $0"
fi
