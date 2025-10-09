<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EstadoCuentaReport implements FromView, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $filters;
    protected $title;
    protected $clientes;

    public function __construct(array $filters, string $title)
    {
        $this->filters = $filters;
        $this->title = $title;
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0.00',
            'J' => '#,##0.00',
            'K' => '#,##0.00',
            'L' => '#,##0.00',
            'M' => '#,##0.00',
            'N' => '#,##0.00',
            'O' => '#,##0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Buscamos todas las filas que sean encabezados (por ejemplo, las que tienen "No Factura")
                // Como son dinámicas, podemos buscar por contenido o establecer un rango fijo si lo sabes.

                // Si todos los encabezados están en las mismas columnas (A a T, por ejemplo),
                // puedes aplicar el estilo a todas las filas que quieras:
                $highestRow = $sheet->getHighestRow();

                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell("B{$row}")->getValue();
                    if ($cellValue === 'No Factura') {
                        // Aplica color de fondo verde suave (D9F2D9)
                        $sheet->getStyle("A{$row}:T{$row}")
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFD9F2D9');
                    }

                    if ($this->filters['filter_currency'] == 1){
                      // Formato con símbolo CRC
                      $sheet->getStyle("O{$row}")
                          ->getNumberFormat()
                          ->setFormatCode('"USD" #,##0.00');
                    }
                    else{
                        // Formato con símbolo CRC
                        $sheet->getStyle("O{$row}")
                            ->getNumberFormat()
                            ->setFormatCode('"CRC" #,##0.00');
                    }
                }
            },
        ];
    }


    public function view(): View
    {
        $clientesQuery = Contact::whereNull('deleted_at')
            ->whereHas('transactionsEstadoCuenta', function ($q) {
                if (!empty($this->filters['filter_date'])) {
                    $range = explode(' to ', $this->filters['filter_date']);
                    if (count($range) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                        $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();
                        $q->whereBetween('transaction_date', [$start, $end]);
                    } else {
                        $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                        $q->whereDate('transaction_date', $singleDate->format('Y-m-d'));
                    }
                }

                if (!empty($this->filters['filter_department'])) {
                    $q->where('department_id', $this->filters['filter_department']);
                }
            })
            ->orderBy('name');

        $clientes = [];
        $clientesQuery->chunk(100, function ($chunk) use (&$clientes) {
            foreach ($chunk as $cliente) {
                $cliente->load(['transactionsEstadoCuenta' => function ($q) {
                    $q->with('payments') // cargar relación payments
                      ->with('location') // cargar relación locations
                      ->with('codigoContable')
                      ->with('caso')
                      ->with('currency')
                      ->orderBy('transaction_date', 'DESC')
                      ->orderBy('consecutivo', 'DESC');
                }]);
                $clientes[] = $cliente;
            }
        });

        $this->clientes = $clientes;

        return view('livewire.reports.exportview.estado-cuenta', [
            'clientes' => $clientes,
            'filters' => $this->filters,
            'title' => $this->title,
        ]);
    }
}
