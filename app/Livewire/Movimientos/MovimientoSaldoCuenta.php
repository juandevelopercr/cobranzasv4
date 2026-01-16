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
    public $totalDisponibleColones = 0;
    public $totalDisponibleDolares = 0;
    public $totalDolarizado = 0;
    public $tipo_cambio = 1;

    protected $rules = [
        'tipo_cambio' => 'required|numeric|min:0',
        'cuentas301.*.saldo_sistema' => 'nullable|numeric',
        'cuentas301.*.pendiente_registro' => 'nullable|numeric',
        'cuentas301.*.traslados_gastos.total_timbres' => 'nullable|numeric',
        'cuentas301.*.traslados_honorarios.total_honorarios' => 'nullable|numeric',
        'cuentas301.*.traslados_karla' => 'nullable|numeric',
        'cuentas301.*.certifondo_bnfa' => 'nullable|numeric',
        'cuentas301.*.colchon' => 'nullable|numeric',
    ];

    public function mount()
    {
        $this->fecha = now()->startOfMonth()->format('d-m-Y') . ' - ' . now()->endOfMonth()->format('d-m-Y');
        $this->loadData();
    }

    public function updated($propertyName, $value)
    {
        // Limpiar formato y convertir a float antes de calcular
        if (str_contains($propertyName, 'cuentas301')) {
            data_set($this, $propertyName, floatval(str_replace(',', '', $value)));
            $this->calculaTotales();
        }

        if ($propertyName === 'tipo_cambio') {
            $this->tipo_cambio = floatval(str_replace(',', '', $value));
            $this->calculaTotales();
        }

        // Emitir evento que Alpine escuchará para reinicializar Cleave
        //$this->dispatch('reinitCleaveControls');
    }

    public function loadData()
    {
        $listacuentas301 = $this->getCuentas(1);
        $listacuentasOtras = $this->getCuentas(0);

        $busines = Business::find(1);
        $this->tipo_cambio = Helpers::formatDecimal($busines->tipo_cambio_reporte);

        $cuentas_301 = $this->CalculaSaldoBancos($listacuentas301);
        $data = $this->calculaSaldoOtrasCuentas($listacuentasOtras);

        $this->cuentas301 = $cuentas_301;
        $this->otrasCuentasColones = $data['saldo_final_crc'];
        $this->otrasCuentasDolares = $data['saldo_final_usd'];

        $this->otrasCuentasColones = Helpers::formatDecimal($this->otrasCuentasColones, 2);
        $this->otrasCuentasDolares = Helpers::formatDecimal($this->otrasCuentasDolares, 2);

        $this->calculaTotales();
        $this->dispatch('reinitCleaveControls');
    }

    public function calculaTotales()
    {
        $this->totalColones301 = collect($this->cuentas301)
            ->filter(fn($cuenta) => ($cuenta['moneda_id'] ?? null) == 16) // filtrar solo moneda_id = 16
            ->sum(function($cuenta) {
                $saldo_sistema      = floatval($cuenta['saldo_sistema'] ?? 0);
                $pendiente_registro = floatval($cuenta['pendiente_registro'] ?? 0);
                $timbres            = floatval($cuenta['traslados_gastos']['total_timbres'] ?? 0);
                $honorarios         = floatval($cuenta['traslados_honorarios']['total_honorarios'] ?? 0);
                $traslados_karla    = floatval($cuenta['traslados_karla'] ?? 0);
                $certifondo_bnfa    = floatval($cuenta['certifondo_bnfa'] ?? 0);
                $colchon            = floatval($cuenta['colchon'] ?? 0);

                return $saldo_sistema - $pendiente_registro - $timbres - $honorarios - $traslados_karla - $certifondo_bnfa - $colchon;
            });

        $this->totalDolares301 = collect($this->cuentas301)
            ->filter(fn($cuenta) => ($cuenta['moneda_id'] ?? null) == 1) // filtrar solo moneda_id = 16
            ->sum(function($cuenta) {
                $saldo_sistema      = floatval($cuenta['saldo_sistema'] ?? 0);
                $pendiente_registro = floatval($cuenta['pendiente_registro'] ?? 0);
                $timbres            = floatval($cuenta['traslados_gastos']['total_timbres'] ?? 0);
                $honorarios         = floatval($cuenta['traslados_honorarios']['total_honorarios'] ?? 0);
                $traslados_karla    = floatval($cuenta['traslados_karla'] ?? 0);
                $certifondo_bnfa    = floatval($cuenta['certifondo_bnfa'] ?? 0);
                $colchon            = floatval($cuenta['colchon'] ?? 0);

                return $saldo_sistema - $pendiente_registro - $timbres - $honorarios - $traslados_karla - $certifondo_bnfa - $colchon;
            });


        $otrasCuentasColones = floatval(str_replace(',', '', $this->otrasCuentasColones));
        $otrasCuentasDolares = floatval(str_replace(',', '', $this->otrasCuentasDolares));

        $this->totalDisponibleColones = $this->totalColones301 + $otrasCuentasColones;
        $this->totalDisponibleDolares = $this->totalDolares301 + $otrasCuentasDolares;

        $tipoCambio = $this->tipo_cambio ?: 1;
        $this->totalDolarizado = $this->totalDisponibleDolares + ($this->totalDisponibleColones / floatval($tipoCambio));

        $this->totalColones301 = Helpers::formatDecimal($this->totalColones301, 2);
        $this->totalDolares301 = Helpers::formatDecimal($this->totalDolares301, 2);

        $this->totalDisponibleColones = Helpers::formatDecimal($this->totalDisponibleColones, 2);
        $this->totalDisponibleDolares = Helpers::formatDecimal($this->totalDisponibleDolares, 2);

        $this->totalDolarizado = Helpers::formatDecimal($this->totalDolarizado, 2);
    }

    public function guardarDatos()
    {
        $cuentas = $this->getCuentas(1);
        $busines = Business::find(1);

        foreach ($this->cuentas301 as $index => $cuentaData) {
            $cuentaId = $cuentas[$index]['id'] ?? null;
            if (!$cuentaId) continue;

            $objCuenta = Cuenta::find($cuentaId);
            if (!$objCuenta) continue;

            $objCuenta->traslados_karla = floatval($cuentaData['traslados_karla'] ?? 0);
            $objCuenta->certifondo_bnfa = floatval($cuentaData['certifondo_bnfa'] ?? 0);
            $objCuenta->colchon = floatval($cuentaData['colchon'] ?? 0);
            $objCuenta->save();
        }

        $busines->tipo_cambio_reporte = $this->tipo_cambio;
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
'banco_ids',
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
            $saldo_sistema = 0;
            $pendiente_registro = 0;
            $traslados_gastos = ['total_timbres'=>0,'total_honorarios'=>0];
            $traslados_honorarios = ['total_timbres'=>0,'total_honorarios'=>0];

            $dataDate = $this->getDateStartAndDateEnd(null, true);
            $status = 'REGISTRADO';

            $data = Helpers::calculaBalance([$item['id']], $dataDate, $status, false);
            $saldo_sistema = floatval($data['saldo_final_crc'] + $data['saldo_final_usd']);

            if ($item['calcular_pendiente_registro']) {
                $pendiente_registro = floatval($this->calculaPendienteRegistro($item['id']));
            }

            if ($item['calcular_traslado_gastos']) {
                $traslados_gastos = $this->calculaPendienteGastoAndHonorarios($item, 'GASTO');
            }

            if ($item['calcular_traslado_honorarios']) {
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
                'traslados_karla' => floatval($item['traslados_karla']),
                'certifondo_bnfa' => floatval($item['certifondo_bnfa']),
                'colchon' => floatval($item['colchon']),
                'saldo_disponible' => 0,
            ];
        }
        return $cuentas;
    }

    public function calculaPendienteRegistro($cuenta_id)
    {
        $departamentos = CuentaHasDepartment::where('cuenta_id', $cuenta_id)
            ->pluck('department_id')->toArray();

        $dato = DB::table('transactions_lines')
            ->selectRaw("COALESCE(SUM(
                CASE
                    WHEN transactions.currency_id = 16 THEN transactions_lines.timbres
                    ELSE transactions_lines.timbres * COALESCE(transactions.proforma_change_type, 1)
                END
            ), 0) as total")
            ->join('transactions', 'transactions_lines.transaction_id', '=', 'transactions.id')
            ->join('products', 'transactions_lines.product_id', '=', 'products.id')
            ->whereNull('transactions_lines.fecha_pago_registro')
            ->where('products.type_notarial_act', 'GASTO')
            ->where('transactions.proforma_status', Transaction::FACTURADA)
            ->whereNotNull('transactions.fecha_traslado_gasto')
            ->when(!empty($departamentos), fn($q) => $q->whereIn('transactions.department_id', $departamentos))
            ->first();

        return floatval($dato->total ?? 0);
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

        $query = DB::table('transactions as t')
            ->selectRaw("
                SUM(
                    COALESCE(
                        CASE
                            -- Moneda de la cuenta: USD
                            WHEN {$cuenta['moneda_id']} = 1 THEN
                                CASE
                                    WHEN t.currency_id = 16 THEN COALESCE(t.totalTimbres,0) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                                    ELSE COALESCE(t.totalTimbres,0)
                                END
                            -- Moneda de la cuenta: colones/local (16)
                            ELSE
                                CASE
                                    WHEN t.currency_id = 1 THEN COALESCE(t.totalTimbres,0) * NULLIF(COALESCE(t.proforma_change_type,1),0)
                                    ELSE COALESCE(t.totalTimbres,0)
                                END
                        END,
                    0)
                ) AS total_timbres,

                SUM(
                    COALESCE(
                        CASE
                            -- Moneda de la cuenta: USD
                            WHEN {$cuenta['moneda_id']} = 1 THEN
                                CASE
                                    WHEN t.currency_id = 16 THEN
                                        (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0))
                                        / NULLIF(COALESCE(t.proforma_change_type,1),0)
                                    ELSE
                                        (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0))
                                END
                            -- Moneda de la cuenta: colones/local
                            ELSE
                                CASE
                                    WHEN t.currency_id = 1 THEN
                                        (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0))
                                        * NULLIF(COALESCE(t.proforma_change_type,1),0)
                                    ELSE
                                        (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0))
                                END
                        END,
                    0)
                ) AS total_honorarios

            ")
            ->join('contacts', 't.contact_id', '=', 'contacts.id')
            ->join('currencies', 't.currency_id', '=', 'currencies.id')
            ->join('banks', 't.bank_id', '=', 'banks.id')
            ->whereNotNull('t.fecha_deposito_pago')
            ->whereIn('t.id', $subQuery1)
            ->whereNotIn('banks.id', [3])
            ->where('t.currency_id', $cuenta['moneda_id'])
            ->whereNotNull('t.numero_deposito_pago')
            ->whereNull("t.$field_fecha")
            ->where('t.proforma_status', Transaction::FACTURADA)
            ->whereIn('t.id', $subQuery2)
            ->when(
                !empty($cuenta['banco_ids']) || !empty($cuenta['banco_id']),
                fn($q) => $q->whereIn('t.bank_id', !empty($cuenta['banco_ids']) ? $cuenta['banco_ids'] : [$cuenta['banco_id']])
            )
            ->when(!empty($departamentos), fn($q) => $q->whereIn('t.department_id', $departamentos));

        $dato = $query->first();

        return [
            'total_timbres' => $type === 'GASTO' ? floatval($dato->total_timbres ?? 0) : 0,
            'total_honorarios' => $type !== 'GASTO' ? floatval($dato->total_honorarios ?? 0) : 0,
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
            $dataDate = $this->getDateStartAndDateEnd(null, true);
            $balance = Helpers::calculaBalance([$cuenta['id']], $dataDate, $status, false);

            if ($cuenta['moneda_id'] == 16) {
                $saldo_final_crc += floatval($balance['saldo_final_crc']);
            } else {
                $saldo_final_usd += floatval($balance['saldo_final_usd']);
            }
        }

        return [
            'saldo_final_crc' => $saldo_final_crc,
            'saldo_final_usd' => $saldo_final_usd,
        ];
    }

}
