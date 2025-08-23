<?php

namespace App\Livewire\Contacts\Contactos;

use App\Livewire\BaseComponent;
use App\Models\AreaPractica;
use App\Models\Bank;
use App\Models\ContactContacto;
use App\Models\DataTableConfig;
use App\Models\GrupoEmpresarial;
use App\Models\Sector;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ContactoManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'htSearch', history: true)]
  public $search = '';

  #[Url(as: 'htSortBy', history: true)]
  public $sortBy = 'contacts_contactos.name';

  #[Url(as: 'htSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'htPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $contact_id;
  public $name;
  public $email;
  public $telefono;
  public $ext;
  public $celular;
  public $grupo_empresarial_id;
  public $sector_id;
  public $area_practica_id;
  public $clasificacion;
  public $tipo_cliente;
  public $fecha_nacimiento;
  public $anno_ingreso;

  public $closeForm = false;

  public $columns;
  public $defaultColumns;

  public $grupos;
  public $areas;
  public $sectores;
  public $tipos;
  public $clasificaciones;
  public $contactName;

  public $areasPracticas;
  public $sectoresIndustriales;

  protected function getModelClass(): string
  {
    return ContactContacto::class;
  }

  public function mount($contact_id, $contactName)
  {
    $this->contact_id = $contact_id;
    $this->contactName = $contactName;
    $this->grupos = GrupoEmpresarial::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->areas = AreaPractica::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->sectores = Sector::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->tipos = [['id' => 'ACTUAL', 'name' => 'ACTUAL'], ['id' => 'EXCLIENTE', 'name' => 'EXCLIENTE']];
    $this->clasificaciones = [['id' => 'RECURRENTE', 'name' => 'RECURRENTE'], ['id' => 'OCASIONAL', 'name' => 'OCASIONAL']];
    $this->refresDatatable();
  }

  public function render()
  {
    $records = ContactContacto::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('contacts_contactos.contact_id', '=', $this->contact_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.contacts.contactos.datatable', [
      'records' => $records,
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->action = 'create';
    $this->dispatch('reinitContactContactSelec2Form'); // Enviar evento al frontend
    $this->dispatch('scroll-to-top');
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate([
      'contact_id' => 'required|exists:contacts,id',
      'name' => 'nullable|max:255',
      'email' => 'required|email|max:59',
      'telefono' => 'required|max:14',
      'ext' => 'nullable|max:6',
      'celular' => 'nullable|max:14',
      'grupo_empresarial_id' => 'nullable|exists:grupos_empresariales,id',
      'sector_id' => 'nullable|exists:sectores,id',
      'area_practica_id' => 'nullable|exists:areas_practicas,id',
      'clasificacion' => 'nullable|in:' . implode(',', ContactContacto::CLASIFICACIONES),
      'tipo_cliente' => 'nullable|in:' . implode(',', ContactContacto::TIPOS_CLIENTE),
      'fecha_nacimiento' => 'nullable|date',
      'anno_ingreso' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
    ], [
      'contact_id.required' => 'El contacto es obligatorio',
      'contact_id.exists' => 'El contacto seleccionado no existe',
      'email.required' => 'El email es obligatorio',
      'email.email' => 'Debe ser un email válido',
      'telefono.required' => 'El teléfono es obligatorio',
      'grupo_empresarial_id.exists' => 'El grupo empresarial no existe',
      'sector_id.exists' => 'El sector no existe',
      'area_practica_id.exists' => 'El área de práctica no existe',
      'clasificacion.in' => 'Clasificación inválida',
      'tipo_cliente.in' => 'Tipo de cliente inválido',
      'fecha_nacimiento.date' => 'Formato de fecha inválido',
      'anno_ingreso.integer' => 'Debe ser un año válido',
      'anno_ingreso.min' => 'El año debe ser mayor a 1900',
      'anno_ingreso.max' => 'El año no puede ser futuro',
    ], [
      'contact_id' => 'contacto',
      'name' => 'nombre',
      'email' => 'correo electrónico',
      'telefono' => 'teléfono',
      'ext' => 'ext',
      'celular' => 'celular',
      'grupo_empresarial_id' => 'grupo empresarial',
      'sector_id' => 'sector',
      'area_practica_id' => 'área de práctica',
      'clasificacion' => 'clasificación',
      'tipo_cliente' => 'tipo de cliente',
      'fecha_nacimiento' => 'fecha de nacimiento',
      'anno_ingreso' => 'año de ingreso',
    ]);

    try {

      $validatedData['fecha_nacimiento'] = !empty($this->fecha_nacimiento) ? Carbon::parse($this->fecha_nacimiento)->format('Y-m-d')  : NULL;

      // Crear el usuario con la contraseña encriptada
      $record = ContactContacto::create([
        'contact_id'                    => $validatedData['contact_id'],
        'name'                          => $validatedData['name'],
        'email'                         => $validatedData['email'],
        'telefono'                      => $validatedData['telefono'],
        'ext'                           => $validatedData['ext'],
        'celular'                       => $validatedData['celular'] ?? 0,
        'grupo_empresarial_id'          => $validatedData['grupo_empresarial_id'] ?? 0,
        'sector_id'                     => $validatedData['sector_id'] ?? 0,
        'area_practica_id'              => $validatedData['area_practica_id'] ?? 0,
        'clasificacion'                 => $validatedData['clasificacion'] ?? 0,
        'tipo_cliente'                  => $validatedData['tipo_cliente'] ?? 0,
        'fecha_nacimiento'              => $validatedData['fecha_nacimiento'] ?? NULL,
        'anno_ingreso'                  => $validatedData['anno_ingreso'] ?? 0,
      ]);

      $record->areasPracticas()->sync($this->areasPracticas);
      $record->sectoresIndustriales()->sync($this->sectoresIndustriales);

      $closeForm = $this->closeForm;

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
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

    $record = ContactContacto::find($recordId);
    $this->recordId = $recordId;

    $this->contact_id             = $record->contact_id;
    $this->name                   = $record->name;
    $this->email                  = $record->email;
    $this->telefono               = $record->telefono;
    $this->ext                    = $record->ext;
    $this->celular                = $record->celular;
    $this->grupo_empresarial_id   = $record->grupo_empresarial_id;
    $this->sector_id              = $record->sector_id;
    $this->area_practica_id       = $record->area_practica_id;
    $this->clasificacion          = $record->clasificacion;
    $this->tipo_cliente           = $record->tipo_cliente;
    $this->fecha_nacimiento       = $record->fecha_nacimiento;
    $this->anno_ingreso           = $record->anno_ingreso;

    $this->fecha_nacimiento = !empty($record->fecha_nacimiento) ? Carbon::parse($record->fecha_nacimiento)->format('d-m-Y') : $record->fecha_nacimiento;

    $this->areasPracticas = $record->areasPracticas->pluck('id')->toArray();
    $this->sectoresIndustriales = $record->sectoresIndustriales->pluck('id')->toArray();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
  }

  public function update()
  {
    $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
    $recordId = $this->recordId;

    // Valida los datos
    $validatedData = $this->validate([
      'contact_id' => 'required|exists:contacts,id',
      'name' => 'nullable|max:255',
      'email' => 'required|email|max:59',
      'telefono' => 'required|max:14',
      'ext' => 'nullable|max:6',
      'celular' => 'nullable|max:14',
      'grupo_empresarial_id' => 'nullable|exists:grupos_empresariales,id',
      'sector_id' => 'nullable|exists:sectores,id',
      'area_practica_id' => 'nullable|exists:areas_practicas,id',
      'clasificacion' => 'nullable|in:' . implode(',', ContactContacto::CLASIFICACIONES),
      'tipo_cliente' => 'nullable|in:' . implode(',', ContactContacto::TIPOS_CLIENTE),
      'fecha_nacimiento' => 'nullable|date',
      'anno_ingreso' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
    ], [
      'contact_id.required' => 'El contacto es obligatorio',
      'contact_id.exists' => 'El contacto seleccionado no existe',
      'email.required' => 'El email es obligatorio',
      'email.email' => 'Debe ser un email válido',
      'telefono.required' => 'El teléfono es obligatorio',
      'grupo_empresarial_id.exists' => 'El grupo empresarial no existe',
      'sector_id.exists' => 'El sector no existe',
      'area_practica_id.exists' => 'El área de práctica no existe',
      'clasificacion.in' => 'Clasificación inválida',
      'tipo_cliente.in' => 'Tipo de cliente inválido',
      'fecha_nacimiento.date' => 'Formato de fecha inválido',
      'anno_ingreso.integer' => 'Debe ser un año válido',
      'anno_ingreso.min' => 'El año debe ser mayor a 1900',
      'anno_ingreso.max' => 'El año no puede ser futuro',
    ], [
      'contact_id' => 'contacto',
      'name' => 'nombre',
      'email' => 'correo electrónico',
      'telefono' => 'teléfono',
      'ext' => 'ext',
      'celular' => 'celular',
      'grupo_empresarial_id' => 'grupo empresarial',
      'sector_id' => 'sector',
      'area_practica_id' => 'área de práctica',
      'clasificacion' => 'clasificación',
      'tipo_cliente' => 'tipo de cliente',
      'fecha_nacimiento' => 'fecha de nacimiento',
      'anno_ingreso' => 'año de ingreso',
    ]);

    try {
      // Encuentra el registro existente
      $record = Contactcontacto::findOrFail($recordId);

      $validatedData['fecha_nacimiento'] = !empty($this->fecha_nacimiento) ? Carbon::parse($this->fecha_nacimiento)->format('Y-m-d')  : NULL;

      // Actualiza el usuario
      $record->update([
        'contact_id'                    => $validatedData['contact_id'],
        'name'                          => $validatedData['name'],
        'email'                         => $validatedData['email'],
        'telefono'                      => $validatedData['telefono'],
        'ext'                           => $validatedData['ext'],
        'celular'                       => $validatedData['celular'] ?? 0,
        'grupo_empresarial_id'          => $validatedData['grupo_empresarial_id'] ?? 0,
        'sector_id'                     => $validatedData['sector_id'] ?? 0,
        'area_practica_id'              => $validatedData['area_practica_id'] ?? 0,
        'clasificacion'                 => $validatedData['clasificacion'] ?? 0,
        'tipo_cliente'                  => $validatedData['tipo_cliente'] ?? 0,
        'fecha_nacimiento'              => $validatedData['fecha_nacimiento'] ?? 0,
        'anno_ingreso'                  => $validatedData['anno_ingreso'] ?? 0,
      ]);

      $record->areasPracticas()->sync($this->areasPracticas);
      $record->sectoresIndustriales()->sync($this->sectoresIndustriales);

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
      $record = Contactcontacto::findOrFail($recordId);

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
      'name',
      'email',
      'telefono',
      'ext',
      'celular',
      'grupo_empresarial_id',
      'sector_id',
      'area_practica_id',
      'clasificacion',
      'tipo_cliente',
      'fecha_nacimiento',
      'anno_ingreso'
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

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function updated($property)
  {
    $this->dispatch('reinitContactContactSelec2Form'); // Reaplica select2 después de cada actualización
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'contactContact-datatable')
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
    'filter_grupo' => NULL,
    'filter_name' => NULL,
    'filter_email' => NULL,
    'filter_tipo' => NULL,
    'filter_area' => NULL,
    'filter_clasificacion' => NULL,
    'filter_sector' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'grupo',
        'orderName' => 'grupo',
        'label' => __('Grupo empresarial'),
        'filter' => 'filter_grupo',
        'filter_type' => 'select',
        'filter_sources' => 'grupos',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => '15%',
        'visible' => true,
      ],
      [
        'field' => 'name',
        'orderName' => 'contacts_contactos.name',
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
        'field' => 'email',
        'orderName' => 'contacts_contactos.email',
        'label' => __('Email'),
        'filter' => 'filter_email',
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
        'field' => 'tipo_cliente',
        'orderName' => 'contacts_contactos.tipo_cliente',
        'label' => __('Tipo de cliente'),
        'filter' => 'filter_tipo',
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
        'field' => 'area',
        'orderName' => '',
        'label' => __('Area de práctica'),
        'filter' => 'filter_area',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnArea',
        'parameters' => [''],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => '80px',
        'visible' => true,
      ],
      [
        'field' => 'clasificacion',
        'orderName' => 'contacts_contactos.clasificacion',
        'label' => __('Clasificación'),
        'filter' => 'filter_clasificacion',
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
        'field' => 'sector',
        'orderName' => '',
        'label' => __('Sector'),
        'filter' => 'filter_sector',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnSector',
        'parameters' => [''],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => '80px',
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

  public function resetFilters()
  {
    $this->reset('filters');
    $this->selectedIds = [];
  }
}
