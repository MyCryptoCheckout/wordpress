#!/bin/bash
# Updates the SDK by resetting, pulling and then renaming again.

if [ ! -f "sdk_namespace" ]; then
	echo "The file sdk_namespace, that is created by sdk_rename.sh, was not detected. Automatic update and renaming is not possible until the file has either been created or sdk_rename.sh is run for the first time."
	exit 1
fi

source sdk_namespace

if [ "$NAMESPACE" == "" ]; then
	echo "The sdk_namespace file is missing, meaning that you probably haven't run sdk_rename.sh"
	echo "See the rename script for more information."
	exit 1
fi

if [ ! -f "../base.php" ]; then
	echo "Please run this file from the scripts directory."
	exit 1
fi

source sdk_restore.sh

git pull

./sdk_rename.sh $NAMESPACE
