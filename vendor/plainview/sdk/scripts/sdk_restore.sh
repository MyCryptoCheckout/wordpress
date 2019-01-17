#!/bin/bash
# Restores the SDK namespace by resetting git.

# The namespace will no longer be valid after a reset.
if [ -f "sdk_namespace" ]; then
	rm sdk_namespace
fi

# Reset all files to their original state, including their namespaces.
git reset --hard
