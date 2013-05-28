#!/bin/bash

SCRIPT_BASENAME=`basename "$0" .sh`
SCRIPT_PATH=`readlink -f "$0"`
SCRIPT_PATH=`dirname "${SCRIPT_PATH}"`
export PATH="${SCRIPT_PATH}:${PATH}"
export LD_LIBRARY_PATH="${SCRIPT_PATH}:${LD_LIBRARY_PATH}"

"${SCRIPT_PATH}/${SCRIPT_BASENAME}"
