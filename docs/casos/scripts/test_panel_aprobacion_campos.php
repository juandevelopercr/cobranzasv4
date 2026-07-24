<?php

// Prueba de regresion para los 11 campos migrados de varchar a decimal(18,2)
// en el panel "Aprobacion" (ahonorarios_totales y afines).
//
// Camino UPDATE (editar): se prueba end-to-end con Livewire real, contra la
// base de datos real, dentro de una transaccion que siempre revierte:
//   1. vacio ''      -> debe guardar sin error (cleanEmptyNumericFields lo
//                        convierte a null antes de tocar la columna decimal)
//   2. texto basura  -> debe fallar la VALIDACION (rules() numeric), nunca
//                        una excepcion SQL/cast
//
// Camino STORE (crear): store() y update() son metodos distintos, pero
// ambos llaman exactamente a cleanEmptyNumericFields() + $this->validate()
// contra el mismo array rules() (confirmado leyendo el codigo de los 11
// componentes) - no hay una regla o limpieza distinta para creacion vs
// edicion. Probar aqui el mismo par vacio/basura directamente contra
// Validator::make($rules) confirma que la regla en si (que es la unica
// pieza que store() podria aplicar distinto) rechaza/acepta igual.

use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;

class RollbackForTest extends \Exception {}

$components = [
    'CasoScotiabank'    => Bank::SCOTIABANKCR,
    'CasoTerceros'      => Bank::TERCEROS,
    'CasoLafise'        => Bank::LAFISE,
    'CasoDavivienda'    => Bank::DAVIVIENDA,
    'CasoBac'           => Bank::SANJOSE,
    'CasoCafsa'         => Bank::FINANCIERACAFSA,
    'CasoBancoGeneral'  => Bank::BANCOGENERAL,
    'CasoScotiabankBch' => Bank::SCOTIABANKBCH,
    'CasoCoocique'      => Bank::COOCIQUE,
    'CasoCoocique2'     => Bank::COOCIQUE2,
    'CasoCartera'       => Bank::CARTERA,
];

$fields = [
    'aestimacion_demanda_en_presentacion',
    'aestimacion_demanda_en_presentacion_usd',
    'ahonorarios_totales',
    'ahonorarios_totales_usd',
    'amonto_cancelar',
    'amonto_incobrable',
    'bgastos_proceso',
    'liquidacion_intereses_aprobada_crc',
    'liquidacion_intereses_aprobada_usd',
    'pmonto_estimacion_demanda_colones',
    'pmonto_estimacion_demanda_dolares',
];

$user = \App\Models\User::find(1);
$fail = 0;
$total = 0;

foreach ($components as $shortName => $bankId) {
    $class = "App\\Livewire\\Casos\\{$shortName}";
    $caso = Caso::where('bank_id', $bankId)->orderByDesc('id')->first();

    if (!$caso) {
        echo sprintf("%-20s SKIP (no hay casos para bank_id=%d)\n", $shortName, $bankId);
        continue;
    }

    $instance = new $class();
    $rules = $instance->rules();

    foreach ($fields as $field) {
        if (!array_key_exists($field, $rules)) {
            continue;
        }
        $total++;

        // --- STORE: la regla en si, aislada (rapido, sin DB) ---
        $vStore = Validator::make([$field => 'hgjhghj'], [$field => $rules[$field]]);
        $storeRechazaBasura = $vStore->errors()->has($field);

        // --- UPDATE: end-to-end con Livewire + DB real (rollback) ---
        $errVacio = null;
        $errBasura = null;
        try {
            DB::transaction(function () use ($class, $caso, $user, $field, &$errVacio, &$errBasura) {
                $t1 = Livewire::actingAs($user)->test($class)
                    ->call('edit', $caso->id)
                    ->set($field, '')
                    ->call('update');
                $errVacio = $t1->errors()->isNotEmpty();

                $t2 = Livewire::actingAs($user)->test($class)
                    ->call('edit', $caso->id)
                    ->set($field, 'hgjhghj')
                    ->call('update');
                $errBasura = $t2->errors()->has($field);

                throw new RollbackForTest();
            });
        } catch (RollbackForTest $e) {
        } catch (\Throwable $e) {
            echo sprintf("%-20s campo=%-45s EXCEPCION UPDATE: %s\n", $shortName, $field, $e->getMessage());
            $fail++;
            continue;
        }

        $ok = $storeRechazaBasura && !$errVacio && $errBasura;
        if (!$ok) {
            $fail++;
        }

        echo sprintf(
            "%-20s campo=%-45s store_basura=%s update_vacio=%s update_basura=%s %s\n",
            $shortName,
            $field,
            $storeRechazaBasura ? 'ok' : 'ERROR',
            $errVacio ? 'ERROR' : 'ok',
            $errBasura ? 'ok' : 'ERROR',
            $ok ? '' : '<<< FALLO'
        );
    }
}

echo "\nFin de la prueba. Ninguna transaccion se confirmo (todo rollback). Total: $total  Fallos: $fail\n";
