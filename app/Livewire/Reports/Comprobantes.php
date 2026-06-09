<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Comprobante;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Comprobantes extends Component
{
    public $filter_date;
    public $filter_emisor;
    public $filter_receptor;
    public $filter_tipo_documento;
    public $filter_estado_hacienda;
    public $filter_moneda;

    public $loading = false;

    public $listTiposDocumento = [];
    public $listEstadosHacienda = [];

    protected $listeners = [
        'dateRangeSelected' => 'dateRangeSelected',
    ];

    public function mount()
    {
        $this->listTiposDocumento = [
            ['id' => '01', 'name' => 'Factura Electrónica'],
            ['id' => '02', 'name' => 'Nota de Débito'],
            ['id' => '03', 'name' => 'Nota de Crédito'],
            ['id' => '04', 'name' => 'Tiquete Electrónico'],
            ['id' => '05', 'name' => 'Confirmación de aceptación del comprobante electrónico'],
            ['id' => '06', 'name' => 'Confirmación de aceptación parcial del comprobante electrónico'],
            ['id' => '07', 'name' => 'Confirmación de rechazo del comprobante electrónico'],
            ['id' => '08', 'name' => 'Factura de Compra'],
            ['id' => '09', 'name' => 'Confirmación de aceptación'],
            ['id' => '10', 'name' => 'Recibo Electrónico de Pago']
        ];

        $this->listEstadosHacienda = [
            ['id' => 'PENDIENTE', 'name' => 'PENDIENTE'],
            ['id' => 'RECIBIDA', 'name' => 'RECIBIDA'],
            ['id' => 'ACEPTADA', 'name' => 'ACEPTADA'],
            ['id' => 'RECHAZADA', 'name' => 'RECHAZADA'],
        ];

        $this->dispatch('reinitFormControls');
    }

    public function render()
    {
        return view('livewire.reports.comprobantes');
    }

    public function dateRangeSelected($id, $range)
    {
        $this->$id = $range;
    }

    public function exportExcel()
  {
    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'              => $this->filter_date,
        'filter_emisor'            => $this->filter_emisor,
        'filter_receptor'          => $this->filter_receptor,
        'filter_tipo_documento'    => $this->filter_tipo_documento,
        'filter_estado_hacienda'   => $this->filter_estado_hacienda,
        'filter_moneda'            => $this->filter_moneda,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.comprobantes.download', ['key' => $key]));
  }
}