<?php

namespace App\Livewire\Clasificadores\CasosListadoJuzgados;

use App\Models\CasoListadoJuzgado;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\BaseComponent;
use App\Models\DataTableConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class CasoListadoJuzgadoManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $sortBy = 'listado_juzgados.nombre';

  #[Url(history: true)]
  public $sortDir = 'ASC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public string $nombre = '';
  public string $codigo = '';
  public $activo = 1;

  public $closeForm = false;
  public $columns;
  public $defaultColumns;
  public $listActives;

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string
  {
    return CasoListadoJuzgado::class;
  }

  public function mount()
  {
    $this->listActives = [['id' => 1, 'name' => 'Si'], ['id' => 0, 'name' => 'No']];
    $this->refresDatatable();
  }

  public function render()
  {
    $records = CasoListadoJuzgado::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.clasificadores.casos-listado-juzgados.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    $rules = [
      'nombre'      => 'required|string|max:100',
      'codigo'      => 'required|string|max:20',
      'activo'      => 'required|integer|in:0,1',
    ];

    return $rules;
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'nombre.required' => 'El nombre es obligatoria.',
      'nombre.max' => 'El nombre no debe superar los 100 caracteres.',
      'codigo.required' => 'El c>digo es obligatorio.',
      'nombre.codigo' => 'El código no debe superar los 20 caracteres.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'nombre' => 'nombre',
      'codigo' => 'código',
      'activo' => 'activo',
    ];
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    try {
      // Crear el usuario con la contraseña encriptada
      $record = CasoListadoJuzgado::create($validatedData);

      $closeForm = $this->closeForm;

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = CasoListadoJuzgado::find($recordId);
    $this->recordId = $recordId;

    $this->nombre = $record->nombre;
    $this->codigo = $record->codigo;
    $this->activo = $record->activo;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->dispatch('reinitSelect2Controls');

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;

    // Validación de los datos de entrada
    $validatedData = $this->validate();
    try {
      // Encuentra el registro existente
      $record = CasoListadoJuzgado::findOrFail($recordId);

      // Actualiza el usuario
      $record->update($validatedData);

      $closeForm = $this->closeForm;

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }
    } catch (\Exception $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $record = CasoListadoJuzgado::findOrFail($recordId);

      if ($record->delete()) {

        $this->selectedIds = array_filter(
          $this->selectedIds,
          fn($selectedId) => $selectedId != $recordId
        );

        // Opcional: limpiar "seleccionar todo" si ya no aplica
        if (empty($this->selectedIds)) {
          $this->selectAll = false;
        }

        // Emitir actualización
        $this->dispatch('updateSelectedIds', $this->selectedIds);

        // Puedes emitir un evento para redibujar el datatable o actualizar la lista
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(
      'nombre',
      'codigo',
      'activo'
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function setSortBy($sortByField)
  {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'classifier-casos-juzgados-datatable')
      ->first();

    if ($config) {
      // Verifica si ya es un array o si necesita decodificarse
      $columns = is_array($config->columns) ? $config->columns : json_decode($config->columns, true);
      $this->columns = array_values($columns); // Asegura que los índices se mantengan correctamente
      $this->perPage = $config->perPage  ?? 10; // Valor por defecto si viene null
    } else {
      $this->columns = $this->getDefaultColumns();
      $this->perPage = 10;
    }
  }

  public $filters = [
    'filter_codigo' => NULL,
    'filter_name' => NULL,
    'filter_active' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'codigo',
        'orderName' => 'codigo',
        'label' => __('Code'),
        'filter' => 'filter_codigo',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'nombre',
        'orderName' => 'nombre',
        'label' => __('Name'),
        'filter' => 'filter_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'activo',
        'orderName' => 'activo',
        'label' => __('Active'),
        'filter' => 'filter_active',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Actions'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];

    return $this->defaultColumns;
  }

  public function storeAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de almacenamiento
    $this->store();
  }

  public function updateAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function resetFilters()
  {
    $this->reset('filters');
    $this->selectedIds = [];
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  public function updatedActivo($value)
  {
    $this->activo = (int) $value;
  }
}
