<?php

namespace App\Livewire\Movimientos;

use Carbon\Carbon;
use App\Models\Cuenta;
use Livewire\Component;
use App\Helpers\Helpers;
use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\CuentaHasDepartment;
use App\Livewire\Movimientos\Export\SaldosCuentasExport;

class MovimientoSaldoCuenta extends Component
{
    public $fecha;
    public $cuentas301 = [];
    public $otrasCuentasColones = 0;
    public $otrasCuentasDolares = 0;

    // totales
    public $totalColones301 = 0;
    public $totalDolares301 = 0;
    public $totalOtrasCuentasColones = 0;
    public $totalOtrasCuentasDolares = 0;
    public $totalDisponibleColones = 0;
    public $totalDisponibleDolares = 0;
    public $totalDolarizado = 0;
    public $tipo_cambio = 1;

    public function mount()
    {
        //$this->configuracion = Configuracion::find(1);
        $this->fecha = now()->startOfMonth()->format('d-m-Y') . ' - ' . now()->endOfMonth()->format('d-m-Y');

        $this->loadData();
    }

    public function updatedCuentas301()
    {
        //$this->calcularTotales();
    }

    public function loadData()
    {
        $listacuentas301 = $this->getCuentas(1);
        $listacuentasOtras = $this->getCuentas(0);

        // Obtener el primer y último día del mes actual
        $fechaInicio = Carbon::now()->startOfMonth()->format('d-m-Y');
        $fechaFin = Carbon::now()->endOfMonth()->format('d-m-Y');

        $busines = Business::find(1);
        $this->tipo_cambio = Helpers::formatDecimal($busines->tipo_cambio_reporte);

        // Concatenar en el formato que tenías
        $fecha = $fechaInicio . ' - ' . $fechaFin;

        //echo $fecha; // Ejemplo: "01-09-2025 - 30-09-2025"

        $cuentas_301 = $this->CalculaSaldoBancos($listacuentas301);

        $data = $this->calculaSaldoOtrasCuentas($listacuentasOtras);

        // Si no es una petición Ajax, renderizar la vista normalmente
        $this->cuentas301 = $cuentas_301;
        $this->otrasCuentasColones = $data['saldo_final_crc'];
        $this->otrasCuentasDolares = $data['saldo_final_usd'];
        $this->fecha = $fecha;

        // Calcular totales para el footer
        $this->calculaTotales();
    }

    public function calculaTotales()
    {
        // Total de Cuentas 3-101 en colones y dólares
        $this->totalColones301 = collect($this->cuentas301)->sum(function($cuenta) {
            return ($cuenta['saldo_sistema'] ?? 0)
                - ($cuenta['pendiente_registro'] ?? 0)
                - ($cuenta['traslados_gastos']['total_timbres'] ?? 0)
                - ($cuenta['traslados_honorarios']['total_honorarios'] ?? 0)
                - ($cuenta['traslados_karla'] ?? 0)
                - ($cuenta['certifondo_bnfa'] ?? 0)
                - ($cuenta['colchon'] ?? 0);
        });

        $this->totalDolares301 = collect($this->cuentas301)->sum(function($cuenta) {
            // Aquí agregas la lógica si tus cuentas301 tienen valores en USD
            return 0;
        });

        // Totales de otras cuentas (ya calculadas)
        $this->totalOtrasCuentasColones = $this->otrasCuentasColones;
        $this->totalOtrasCuentasDolares = $this->otrasCuentasDolares;

        // Total disponible
        $this->totalDisponibleColones = $this->totalColones301 + $this->totalOtrasCuentasColones;
        $this->totalDisponibleDolares = $this->totalDolares301 + $this->totalOtrasCuentasDolares;

        // Total dolarizado (Colones + Dólares * tipo_cambio si aplica)
        $this->totalDolarizado = $this->totalDisponibleColones + $this->totalDisponibleDolares;

        // Nota: tipo_cambio es la propiedad de tu componente
    }

    /*
    public function calcularTotales()
    {
        $colones = 0;
        $dolares = 0;

        foreach ($this->cuentas301 as $c) {
            $total = $c['saldo_sistema']
                - $c['pendiente_registro']
                - $c['traslados_gastos']['total_timbres']
                - $c['traslados_honorarios']['total_honorarios']
                - $c['traslados_karla']
                - $c['certifondo_bnfa']
                - $c['colchon'];

            if ($c['moneda_id'] == 16) {
                $colones += $total;
            } elseif ($c['moneda_id'] == 1) {
                $dolares += $total;
            }
        }

        $this->totalDisponibleColones = $colones + $this->otrasCuentasColones;
        $this->totalDisponibleDolares = $dolares + $this->otrasCuentasDolares;

        $tipoCambio = $this->configuracion->tipo_cambio_reporte ?: 1;
        $this->totalDolarizado = $this->totalDisponibleDolares + ($this->totalDisponibleColones / $tipoCambio);
    }
    */

    /*
    public function guardarDatos()
    {
        // Aquí puedes persistir cambios
        dd($this);
        foreach ($this->cuentas301 as $cuentaData) {
            $cuenta = Cuenta::find($cuentaData['id']); // Ajusta el modelo y campo 'id'

            if (!$cuenta) continue;

            $cuenta->saldo_sistema = floatval($cuentaData['saldo_sistema']);
            $cuenta->pendiente_registro = floatval($cuentaData['pendiente_registro']);
            $cuenta->traslados_gastos = [
                'total_timbres' => floatval($cuentaData['traslados_gastos']['total_timbres'] ?? 0)
            ];
            $cuenta->traslados_honorarios = [
                'total_honorarios' => floatval($cuentaData['traslados_honorarios']['total_honorarios'] ?? 0)
            ];
            $cuenta->traslados_karla = floatval($cuentaData['traslados_karla'] ?? 0);
            $cuenta->certifondo_bnfa = floatval($cuentaData['certifondo_bnfa'] ?? 0);
            $cuenta->colchon = floatval($cuentaData['colchon'] ?? 0);

            $cuenta->save();
        }
    }
    */
    public function guardarDatos()
    {
        $cuentas = $this->getCuentas(1);
        $busines = Business::find(1);

        foreach ($this->cuentas301 as $index => $cuentaData) {
            // Aquí tienes la info del Blade en $cuentaData
            $cuentaId = $cuentas[$index]['id'] ?? null;

            if (!$cuentaId) continue;

            $objCuenta = Cuenta::find($cuentaId);
            if (!$objCuenta) continue;

            $trasladosKarla = isset($cuentaData['traslados_karla'])
                ? str_replace(',', '', $cuentaData['traslados_karla'])
                : 0;

            $certifondoBnfa = isset($cuentaData['certifondo_bnfa'])
                ? str_replace(',', '', $cuentaData['certifondo_bnfa'])
                : 0;

            $colchon = isset($cuentaData['colchon'])
                ? str_replace(',', '', $cuentaData['colchon'])
                : 0;

            $objCuenta->traslados_karla = $trasladosKarla;
            $objCuenta->certifondo_bnfa = $certifondoBnfa;
            $objCuenta->colchon = $colchon;
            $objCuenta->save();
        }

        // Guardar tipo de cambio
        $tipoCambio = $this->tipo_cambio ?? 0;
        $busines->tipo_cambio_reporte = $tipoCambio;
        $busines->save();

        session()->flash('message', 'Se ha actualizado el registro satisfactoriamente.');
    }

    public function exportarExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new SaldosCuentasExport(
                $this->cuentas301,
                $this->totalColones301,
                $this->totalDolares301,
                $this->otrasCuentasColones,
                $this->otrasCuentasDolares,
                $this->totalDisponibleColones,
                $this->totalDisponibleDolares,
                $this->totalDolarizado,
                $this->tipo_cambio
            ),
            'saldos_cuentas.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.movimientos.movimiento-saldo-cuenta');
    }

    public function getCuentas($is_cuenta_301 = 1)
    {
        return Cuenta::select([
                'id',
                'nombre_cuenta',
                'moneda_id',
                'calcular_pendiente_registro',
                'calcular_traslado_gastos',
                'calcular_traslado_honorarios',
                'banco_id',
                'traslados_karla',
                'certifondo_bnfa',
                'colchon'
            ])
            ->where('is_cuenta_301', $is_cuenta_301)
            ->get()
            ->toArray();
    }

    public function CalculaSaldoBancos($listacuentas)
    {
        $cuentas = [];
        foreach ($listacuentas as $item) {
            $status = 'REGISTRADO';
            $saldo_sistema = 0;
            //$data = \common\libs\Utiles::calculaBalance($ids, $dataDate, $status, false, false);

            //$saldo_sistema = \common\libs\Utiles::getSaldoCuenta($item['id']);

            $dataDate = $this->getDateStartAndDateEnd(NULL, true);
            $saveSesion = false;
            $status = 'REGISTRADO';

            $data = Helpers::calculaBalance([$item['id']], $dataDate, $status, false);

            $saldo_sistema = $data['saldo_final_crc'] + $data['saldo_final_usd'];

            // AQUI
            $pendiente_registro = 0;
            $traslados_gastos = [
                'total_timbres' => 0,
                'total_honorarios' => 0
            ];
            $traslados_honorarios = [
                'total_timbres' => 0,
                'total_honorarios' => 0
            ];

            if ($item['calcular_pendiente_registro']) {
                $pendiente_registro = $this->calculaPendienteRegistro($item['id']);
            }

            if ($item['calcular_traslado_gastos']) {
                //$traslados_gastos = $this->calculaPendienteGasto($item);
                $field_fecha = 'transactions.fecha_traslado_gasto';
                $traslados_gastos = $this->calculaPendienteGastoAndHonorarios($item, 'GASTO');
            }

            if ($item['calcular_traslado_honorarios']) {
                $field_fecha = 'transactions.fecha_traslado_honorario';
                $traslados_honorarios = $this->calculaPendienteGastoAndHonorarios($item, 'HONORARIO');
            }

            $cuentas[] = [
                'id' => $item['id'],
                'nombre_cuenta' => $item['nombre_cuenta'],
                'moneda_id' => $item['moneda_id'],
                'saldo_sistema' => $saldo_sistema,
                'pendiente_registro' => $pendiente_registro,
                'traslados_gastos' => $traslados_gastos,
                'traslados_honorarios' => $traslados_honorarios,
                'traslados_karla' => $item['traslados_karla'],
                'certifondo_bnfa' => $item['certifondo_bnfa'],
                'colchon' => $item['colchon'],
                'saldo_disponible' => 0,
            ];
        }
        return $cuentas;
    }

    public function calculaPendienteRegistro($cuenta_id)
    {
        // 1. Obtener departamentos asociados a la cuenta
        $departamentos = CuentaHasDepartment::where('cuenta_id', $cuenta_id)
            ->pluck('department_id') // trae solo la columna
            ->toArray();

        // 2. Query para calcular el total
        $dato = DB::table('transactions_lines')
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN transactions.currency_id = 16 THEN transactions_lines.timbres
                        ELSE transactions_lines.timbres * COALESCE(transactions.proforma_change_type, 1)
                    END
                ), 0) as total
            ")
            ->join('transactions', 'transactions_lines.transaction_id', '=', 'transactions.id')
            ->join('products', 'transactions_lines.product_id', '=', 'products.id')
            ->whereNull('transactions_lines.fecha_pago_registro')
            ->where('products.type_notarial_act', 'GASTO')
            ->where('transactions.proforma_status', Transaction::FACTURADA) // constante igual que en Yii2
            ->whereNotNull('transactions.fecha_traslado_gasto')
            ->when(!empty($departamentos), function ($query) use ($departamentos) {
                $query->whereIn('transactions.department_id', $departamentos);
            })
            ->first();

        // 3. Retornar total
        return $dato ? $dato->total : 0;
    }

    public function calculaPendienteGastoAndHonorarios(array $cuenta, string $type)
    {
        $departamentos = DB::table('cuentas_has_departments')
            ->where('cuenta_id', $cuenta['id'])
            ->pluck('department_id')
            ->toArray();

        $subQuery1 = DB::table('transactions_lines')
            ->select('transactions_lines.transaction_id')
            ->distinct()
            ->join('products', 'transactions_lines.product_id', '=', 'products.id')
            ->where('products.type_notarial_act', $type);

        $subQuery2 = DB::table('transactions_commissions')
            ->select('transaction_id')
            ->distinct()
            ->where('centro_costo_id', 1);

        $field_fecha = $type === 'GASTO' ? 'fecha_traslado_gasto' : 'fecha_traslado_honorario';

        // --- Consulta principal simplificada ---
        $query = DB::table('transactions as t')
            ->selectRaw("
                SUM(
                    CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalTimbres,0)
                        ELSE COALESCE(t.totalTimbres,0) * NULLIF(COALESCE(t.proforma_change_type,0),0)
                    END
                ) AS total_timbres,
                SUM(
                    CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)
                        ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)) * NULLIF(COALESCE(t.proforma_change_type,0),0)
                    END
                ) AS total_honorarios
            ")
            ->join('contacts', 't.contact_id', '=', 'contacts.id')
            ->join('currencies', 't.currency_id', '=', 'currencies.id')
            ->join('banks', 't.bank_id', '=', 'banks.id')
            ->whereNotNull('t.fecha_deposito_pago')
            ->whereIn('t.id', $subQuery1)
            ->whereNotIn('banks.id', [3])
            ->where('t.currency_id', $cuenta['moneda_id']) // 🔹 filtro directo por moneda
            ->whereNotNull('t.numero_deposito_pago')
            ->whereNull("t.$field_fecha")
            ->where('t.proforma_status', Transaction::FACTURADA)
            ->whereIn('t.id', $subQuery2)
            ->when(!empty($cuenta['banco_id']), function ($query) use ($cuenta) {
                return $query->where('t.bank_id', $cuenta['banco_id']);
            })
            ->when(!empty($departamentos), function ($query) use ($departamentos) {
                return $query->whereIn('t.department_id', $departamentos);
            });

        $dato = $query->first();

        return [
            'total_timbres' => $type === 'GASTO' ? ($dato->total_timbres ?? 0) : 0,
            'total_honorarios' => $type !== 'GASTO' ? ($dato->total_honorarios ?? 0) : 0,
        ];
    }

    public function getDateStartAndDateEnd($fecha = null, $mesActual = false)
    {
        $DateStart = null;
        $DateEnd = null;

        if (!empty($fecha)) {
            $filter_fechas = explode(' - ', $fecha);
            if (count($filter_fechas) === 2) {
                $DateStart = Carbon::createFromFormat('d-m-Y', trim($filter_fechas[0]))->format('Y-m-d');
                $DateEnd   = Carbon::createFromFormat('d-m-Y', trim($filter_fechas[1]))->format('Y-m-d');
            }
        } elseif ($mesActual) {
            $DateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
            $DateEnd   = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        return [
            'DateStart' => $DateStart,
            'DateEnd'   => $DateEnd,
        ];
    }

    public function calculaSaldoOtrasCuentas(array $cuentas)
    {
        $saldo_final_crc = 0;
        $saldo_final_usd = 0;
        $status = 'REGISTRADO';

        foreach ($cuentas as $cuenta) {
            // Obtener el rango de fechas del mes actual
            $dataDate = $this->getDateStartAndDateEnd(null, true);

            // Calcular el balance usando tu helper/calculadora
            $balance = Helpers::calculaBalance([$cuenta['id']], $dataDate, $status, false);

            // Sumar al saldo según la moneda
            if ($cuenta['moneda_id'] == 16) {
                $saldo_final_crc += $balance['saldo_final_crc'];
            } else {
                $saldo_final_usd += $balance['saldo_final_usd'];
            }
        }

        return [
            'saldo_final_crc' => $saldo_final_crc,
            'saldo_final_usd' => $saldo_final_usd,
        ];
    }

}
