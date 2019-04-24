#!/bin/bash

# thunderbolt controller
CTL="${BASEURL}index.php?/module/thunderbolt/"

# Get the scripts in the proper directories
"${CURL[@]}" "${CTL}get_script/thunderbolt.py" -o "${MUNKIPATH}preflight.d/thunderbolt.py"

# Check exit status of curl
if [ $? = 0 ]; then
	# Make executable
	chmod a+x "${MUNKIPATH}preflight.d/thunderbolt.py"

	# Set preference to include this file in the preflight check
	setreportpref "thunderbolt" "${CACHEPATH}thunderbolt.plist"

else
	echo "Failed to download all required components!"
	rm -f "${MUNKIPATH}preflight.d/thunderbolt.py"

	# Signal that we had an error
	ERR=1
fi
