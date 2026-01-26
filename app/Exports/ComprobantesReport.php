<?php

namespace App\Exports;

use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComprobantesReport extends BaseReport
{
    public function __construct(array $filters)
    {
        parent::__construct($filters, 'Reporte de Comprobantes');
    }

    protected function columns(): array
    {
        return [
            ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
            ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
            ['label' => 'Clave', 'field' => 'key', 'type' => 'string', 'align' => 'center', 'width' => 55],
            ['label' => 'Fecha Emisión', 'field' => 'fecha_emision', 'type' => 'date', 'align' => 'center', 'width' => 20],
            ['label' => 'Tipo Documento', 'field' => 'tipo_documento_description', 'type' => 'string', 'align' => 'left', 'width' => 30],
            ['label' => 'Emisor Nombre', 'field' => 'emisor_nombre', 'type' => 'string', 'align' => 'left', 'width' => 40],
            ['label' => 'Emisor ID', 'field' => 'emisor_numero_identificacion', 'type' => 'string', 'align' => 'left', 'width' => 20],
            ['label' => 'Receptor Nombre', 'field' => 'receptor_nombre', 'type' => 'string', 'align' => 'left', 'width' => 40],
            ['label' => 'Receptor ID', 'field' => 'receptor_numero_identificacion', 'type' => 'string', 'align' => 'left', 'width' => 20],
            ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 10],
            ['label' => 'Tipo Cambio', 'field' => 'tipo_cambio', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
            ['label' => 'Total Impuestos', 'field' => 'total_impuestos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
            ['label' => 'Total Comprobante', 'field' => 'total_comprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
            ['label' => 'Estado Hacienda', 'field' => 'status', 'type' => 'string', 'align' => 'center', 'width' => 15],
            ['label' => 'Mensaje Confirmación', 'field' => 'mensajeConfirmacion', 'type' => 'string', 'align' => 'center', 'width' => 20],
        ];
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Comprobante::query();

        // Aplicamos los mismos filtros que en el componente Livewire/Modelo
        // Podemos reutilizar el scopeSearch del modelo pasando string vacío para value
        // y el array de filtros.

        // Sin embargo, scopeSearch hace unos selects específicos que podrían chocar
        // con lo que queremos si no tenemos cuidado, pero revisando el modelo:
        // $query->select($columns)... where function($q) use ($value)...
        // Si value es '', los OR likes darán true para '%%' lo cual es 'todos',
        // pero cuidado con el rendimiento.
        // Mejor implementamos los filtros directos aquí para ser más limpios.

        if (!empty($this->filters['filter_date'])) {
            $range = explode(' to ', $this->filters['filter_date']);
            if (count($range) === 2) {
                try {
                    $start = Carbon::createFromFormat('d-m-Y', $range[0])->startOfDay();
                    $end = Carbon::createFromFormat('d-m-Y', $range[1])->endOfDay();
                    $query->whereBetween('fecha_emision', [$start, $end]);
                } catch (\Exception $e) {}
            }
        }

        if (!empty($this->filters['filter_emisor'])) {
            $query->where(function ($q) {
                $term = '%' . $this->filters['filter_emisor'] . '%';
                $q->where('emisor_nombre', 'like', $term)
                  ->orWhere('emisor_numero_identificacion', 'like', $term);
            });
        }

        if (!empty($this->filters['filter_receptor'])) {
             $query->where(function ($q) {
                $term = '%' . $this->filters['filter_receptor'] . '%';
                $q->where('receptor_nombre', 'like', $term)
                  ->orWhere('receptor_numero_identificacion', 'like', $term);
            });
        }

        if (!empty($this->filters['filter_tipo_documento'])) {
            $query->where('tipo_documento', $this->filters['filter_tipo_documento']);
        }

        if (!empty($this->filters['filter_estado_hacienda'])) {
            $query->where('status', $this->filters['filter_estado_hacienda']);
        }

        if (!empty($this->filters['filter_moneda'])) {
            $query->where('moneda', $this->filters['filter_moneda']);
        }

        // Ordenar por fecha desc
        $query->orderBy('fecha_emision', 'DESC');

        // Agregar columna calculada para descripción tipo documento si queremos mostrarla bonita
        // Como BaseReport usa propiedades del modelo o atributos, podemos confiar en el accessor
        // Pero BaseReport usa ->get() o similar sobre la query.
        // Cuando se exporta, BaseReport usa map() que accede a $row->field.
        // El accessor `tipo_documento_description` existe en el modelo.
        // Pero en map() se usa $col['field']. Si pongo 'tipo_documento_description', funcionará si está en $appends o si accedo directo.
        // El accessor se llama `tipoDocumentoDescription` -> atributo `tipo_documento_description`.

        // En columns he puesto 'tipo_documento_desc'. Necesito que coincida.
        // Ajustaré columns arriba para usar el atributo correcto si mapeo manual o accessor.

        return $query;
    }

    // Override map to handle custom calculations/accessors if needed,
    // or simply adjust column field names to match model accessors.
    // field 'tipo_documento_desc' doesn't exist on model, let's fix that in map or columns.

    public function map($row): array
    {
        // Inject the accessor value into a virtual property/array key if needed
        // OR easier: modify map to use a custom logic for specific fields.

        // BaseReport's map iterates columns.
        $mapped = [];
        foreach ($this->columns() as $col) {
            $field = $col['field'];

            if ($field === 'tipo_documento_desc') {
                $value = $row->tipo_documento_description;
            } else {
                $value = $row->{$field} ?? null;
            }

            // Apply BaseReport formatting logic (copy-paste partial or call parent map?
            // Parent map expects $row->{$field} to work.
            // Since I am inside extend, I can use the logic from BaseReport map here.

            // Let's use the BaseReport map logic but customized for this single field override is clumsy.
            // Better: Add the attribute to the model or use a calculated select.

            // But since BaseReport parses types...
            // Let's just fix the field name in columns to match the accessor.
            // Accessor: getTipoDocumentoDescriptionAttribute -> tipo_documento_description

            // So I will change 'tipo_documento_desc' to 'tipo_documento_description' in columns()
            // and simply return parent::map($row).
            // BUT wait, BaseReport::map iterates columns and does $row->{$field}.
            // $row is an Eloquent model (from query()->cursor() or get()).
            // accessing $row->tipo_documento_description triggers the accessor.
            // Perfect.

            // Exception: parent map doesn't return the array directly constructed, it constructs it via collection map.
            // Actually BaseReport map returns array.

            // So if I just change the field name in columns(), it should work.
            // But wait, I put 'tipo_documento_desc' in my code previously. I will fix it now.
        }

        return parent::map($row);
    }

     protected function getColumns($type) { return []; } // Not used but abstract in BaseReport? No, BaseReport has columns() abstract. I implemented it.
}
