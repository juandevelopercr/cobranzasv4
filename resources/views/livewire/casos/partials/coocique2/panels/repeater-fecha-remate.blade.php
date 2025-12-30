@foreach ($fechasRemate as $index => $fecha)
<div class="row g-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container" wire:key="fecha-remate-{{ $index }}">
        <label class="form-label" for="fechasRemate_{{ $index }}_fecha">
            {{ __('Fecha de remate') }}
        </label>
        <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-calendar"></i></span>

            <input
                type="text"
                id="fechasRemate_{{ $index }}_fecha"
                wire:model="fechasRemate.{{ $index }}.fecha"
                x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
                x-init="init($el)"
                class="form-control date-picke @error('fechasRemate.' . $index . '.fecha') is-invalid @enderror"
            >
        </div>
        @error("fechasRemate.$index.fecha")
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container" wire:key="titulo-remate-{{ $index }}">
        <label class="form-label" for="fechasRemate_{{ $index }}_titulo">
            {{ __('Título') }}
        </label>
        <div class="input-group input-group-merge has-validation">
            <input
                type="text"
                id="fechasRemate_{{ $index }}_titulo"
                wire:model="fechasRemate.{{ $index }}.titulo"
                class="form-control @error('fechasRemate.' . $index . '.titulo') is-invalid @enderror"
                placeholder="Ingrese título"
            >
        </div>
        @error("fechasRemate.$index.titulo")
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
      <div class="mt-6">
        <button type="button" wire:click="removeFechaRemate({{ $index }})"
          class="btn btn-danger d-flex align-items-center">
          <i class="bx bx-trash me-1"></i> Eliminar
        </button>
      </div>
    </div>
</div>
@endforeach

<div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
  <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="addFechaRemate"
    wire:loading.attr="disabled" wire:target="addFechaRemate">
    <span wire:loading.remove wire:target="addFechaRemate">
      <span class="fa fa-plus bx-18px me-2"></span>{{ __('Adicionar Fecha') }}
    </span>
    <span wire:loading wire:target="addFechaRemate">
      <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cargando...') }}
    </span>
  </button>
</div>
