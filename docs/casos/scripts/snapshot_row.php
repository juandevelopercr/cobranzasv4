<?php
// Variables via entorno: CERT_ID, CERT_ACTION (save|restore), CERT_FILE
$id = getenv('CERT_ID');
$action = getenv('CERT_ACTION');
$file = getenv('CERT_FILE');

if ($action === 'save') {
    $row = \Illuminate\Support\Facades\DB::table('casos')->where('id', $id)->first();
    file_put_contents($file, json_encode($row));
    echo "SNAPSHOT_OK\n";
} elseif ($action === 'restore') {
    $row = (array) json_decode(file_get_contents($file), true);
    unset($row['id']);
    \Illuminate\Support\Facades\DB::table('casos')->where('id', $id)->update($row);
    echo "RESTORE_OK\n";
} else {
    echo "ACCION_DESCONOCIDA\n";
}
