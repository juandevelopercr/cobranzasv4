#!/bin/bash
# Uso: ./run_certify.sh <bank-slug> <numero-filtro> <label>
set -e
BANK=$1
NUMERO=$2
LABEL=$3
SCRATCH=/tmp/claude-1000/-home-caceres-dev-cobranzasv4/73faeb29-e48d-4b23-8053-09116480e03d/scratchpad
cd /home/caceres/dev/cobranzasv4

ID=$(php artisan tinker --execute="echo \App\Models\Caso::where('pnumero','$NUMERO')->value('id');" 2>/dev/null | tail -1)
echo "Caso id=$ID (pnumero=$NUMERO)"

SNAP_FILE="$SCRATCH/snap_${ID}.json"
CERT_ID=$ID CERT_ACTION=save CERT_FILE=$SNAP_FILE php artisan tinker $SCRATCH/snapshot_row.php

export LD_LIBRARY_PATH=$SCRATCH/debs/extracted/usr/lib/x86_64-linux-gnu:$LD_LIBRARY_PATH
set +e
timeout 280 node $SCRATCH/certify.js "$BANK" "$NUMERO" "$LABEL" > "$SCRATCH/result_${LABEL}.log" 2>&1
NODE_EXIT=$?
set -e

cat "$SCRATCH/result_${LABEL}.log"

CERT_ID=$ID CERT_ACTION=restore CERT_FILE=$SNAP_FILE php artisan tinker $SCRATCH/snapshot_row.php

echo "NODE_EXIT_CODE=$NODE_EXIT"
