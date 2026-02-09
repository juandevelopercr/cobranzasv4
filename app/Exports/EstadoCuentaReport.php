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
                        try {
                            $start = Carbon::parse(trim($range[0]))->startOfDay();
                            $end   = Carbon::parse(trim($range[1]))->endOfDay();
                            $q->whereBetween('transaction_date', [$start, $end]);
                        } catch (\Exception $e) {
                            // Log error or ignore
                        }
                    } else {
                        try {
                            $singleDate = Carbon::parse(trim($this->filters['filter_date']));
                            $q->whereDate('transaction_date', $singleDate->format('Y-m-d'));
                        } catch (\Exception $e) {
                            // Log error or ignore
                        }
                    }
                }

                if (!empty($this->filters['filter_department'])) {
                    $q->where('department_id', $this->filters['filter_department']);
                }

                if (!empty($this->filters['filter_currency'])) {
                    $q->where('currency_id', $this->filters['filter_currency']);
                }
            });

        if (!empty($this->filters['filter_contact'])) {
            $clientesQuery->where('id', $this->filters['filter_contact']);
        }

        $clientesQuery->orderBy('name');

        $clientes = [];
        $filters = $this->filters;

        $clientesQuery->chunk(100, function ($chunk) use (&$clientes, $filters) {
            foreach ($chunk as $cliente) {
                $cliente->load(['transactionsEstadoCuenta' => function ($q) use ($filters) {
                    $q->with('payments') // cargar relación payments
                      ->with('location') // cargar relación locations
                      ->with('codigoContable')
                      ->with('caso')
                      ->with('currency')
                      ->orderBy('transaction_date', 'DESC')
                      ->orderBy('consecutivo', 'DESC');

                    if (!empty($filters['filter_date'])) {
                        $range = explode(' to ', $filters['filter_date']);
                        if (count($range) === 2) {
                            try {
                                $start = Carbon::parse(trim($range[0]))->startOfDay();
                                $end   = Carbon::parse(trim($range[1]))->endOfDay();
                                $q->whereBetween('transaction_date', [$start, $end]);
                            } catch (\Exception $e) {}
                        } else {
                            try {
                                $singleDate = Carbon::parse(trim($filters['filter_date']));
                                $q->whereDate('transaction_date', $singleDate->format('Y-m-d'));
                            } catch (\Exception $e) {}
                        }
                    }

                    if (!empty($filters['filter_department'])) {
                        $q->where('department_id', $filters['filter_department']);
                    }

                    if (!empty($filters['filter_currency'])) {
                        $q->where('currency_id', $filters['filter_currency']);
                    }
                }]);
                $clientes[] = $cliente; // Agregar el cliente procesado al array
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
