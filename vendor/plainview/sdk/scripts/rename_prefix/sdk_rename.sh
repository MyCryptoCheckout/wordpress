#!/bin/bash
# Renames the Plainview SDK to something unique so that each plugin can use its own version.
# Script must be run from plugin base directory (one dir up).

if [ -f "sdk_namespace" ]; then
	echo "The sdk_namespace file exists, meaning that this rename script has previously been run."
	echo "This script refuses to rename the SDK twice. Run the sdk_restore.sh file to reset the SDk namespace."
	exit 1
fi

NAMESPACE=$1
shift

if [ "$NAMESPACE" == "" ]; then
	echo "Syntax: sdk_rename.sh NAMESPACE"
	echo ""
	echo "The NAMESPACE is the new namespace suffix, appended directly after the word 'sdk'"
	exit 1
fi

echo "NAMESPACE=$NAMESPACE" > sdk_namespace

cd ..
cd ..
if [ ! -f "base.php" ]; then
	echo "Please run this file from the scripts directory."
	exit 1
fi

# Single backslash
perl -pi -e "s/plainview\\\\sdk/plainview\\\\${NAMESPACE}sdk/" `find ./ -type f`
# Double backslash
perl -pi -e "s/plainview\\\\\\\\sdk/plainview\\\\\\\\${NAMESPACE}sdk/" `find ./ -type f`
