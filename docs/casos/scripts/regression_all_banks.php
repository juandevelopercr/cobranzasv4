<?php

use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;
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

$user = \App\Models\User::find(1);

foreach ($components as $shortName => $bankId) {
    $class = "App\\Livewire\\Casos\\{$shortName}";

    $caso = Caso::where('bank_id', $bankId)->orderByDesc('id')->first();
    if (!$caso) {
        echo sprintf("%-20s SKIP (no hay casos para bank_id=%d)\n", $shortName, $bankId);
        continue;
    }

    try {
        DB::transaction(function () use ($class, $shortName, $caso, $user) {
            $instance = new $class();
            $rules = $instance->rules();
            $numericField = null;
            foreach ($rules as $field => $fieldRules) {
                if (is_array($fieldRules) && in_array('numeric', $fieldRules, true) && strpos($field, '.') === false) {
                    $numericField = $field;
                    break;
                }
            }

            if (!$numericField) {
                echo sprintf("%-20s SKIP (no se encontro campo numeric en rules())\n", $shortName);
                throw new RollbackForTest();
            }

            $test = Livewire::actingAs($user)
                ->test($class)
                ->call('edit', $caso->id)
                ->set($numericField, '')
                ->call('update');

            $hasErrors = $test->errors()->isNotEmpty();

            echo sprintf(
                "%-20s caso_id=%-6d campo='%s'  %s\n",
                $shortName,
                $caso->id,
                $numericField,
                $hasErrors ? ('FALLO VALIDACION: ' . json_encode($test->errors()->toArray())) : 'OK (guardo sin error SQL)'
            );

            throw new RollbackForTest();
        });
    } catch (RollbackForTest $e) {
        // rollback intencional, no se persiste nada
    } catch (\Throwable $e) {
        echo sprintf("%-20s ERROR: %s\n", $shortName, $e->getMessage());
    }
}

echo "Fin de la regresion. Ninguna transaccion se confirmo (todo rollback).\n";
