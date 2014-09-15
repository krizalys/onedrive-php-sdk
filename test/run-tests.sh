#!/bin/sh

nFailures=0

test() {
	phantomjs run-test.js $1

	if [ 0 != $? ]; then
		nFailures=$(expr $nFailures + 1)
	fi
}

test quota

[ 0 == $nFailures ] && exit 0
exit 1
