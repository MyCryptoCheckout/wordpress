#!/bin/bash
# This script checks for deprecated functions in your code.

SDK_DIR="sdk"

for FUNCTION in \
"\>_(" \
"description_(" \
"labelf(" \
"label_(" \
"option_(" \
"optionf(" \
"p_(" \
"placeholder_(" \
"set_unfiltered_label_(" \
"title_(" \
;
do
	grep -R "$FUNCTION" | grep -v $SDK_DIR
done;
