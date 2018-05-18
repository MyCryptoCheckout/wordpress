#!/bin/bash

echo "Building once..."

./build.sh

echo "Beginning stat loop."

function collect_stats()
{
	STATS=""
	for FILE in $( find . ) ; do
		STAT=`stat $FILE|grep Change`
		STATS="$STATS $STAT"
	done
	echo $STATS|md5sum
}

OLD_STATS=`collect_stats`
while true; do
	NEW_STATS=`collect_stats`
	if [ "$NEW_STATS" != "$OLD_STATS" ] ; then
		echo -n "Rebuilding... "
		./build.sh
		echo "`date` Done."
		OLD_STATS=`collect_stats`
	fi
    sleep 1
done
