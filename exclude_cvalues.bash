#!/bin/bash

echo $#
if [ $# -ne 4 ]
then
    echo "Usage: $0 <csv-file> <colname> <cvalues_file> <start_line>"
    exit 1
fi
CSV_FILE=${1}
COLNAME=${2}
CV_FILE=${3}
START_LINE=${4}
DUMP_DIR=`dirname ${CSV_FILE}`

for cvalue in `cat ${CV_FILE}`
do
    php ./select.php ${CSV_FILE} ${COLNAME} ne ${cvalue} ${START_LINE} ${DUMP_DIR}
done

