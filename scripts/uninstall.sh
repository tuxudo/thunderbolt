#!/bin/bash

# Remove thunderbolt script
rm -f "${MUNKIPATH}preflight.d/thunderbolt.py"

# Remove thunderbolt.plist file
rm -f "${CACHEPATH}thunderbolt.plist"
