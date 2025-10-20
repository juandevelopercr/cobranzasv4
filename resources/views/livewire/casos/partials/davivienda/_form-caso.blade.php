<?php
use App\Models\User;
use App\Models\CasoEstado;
use App\Models\CasoProducto;
$show = false;
//@if (in_array($this->product_id, [CasoProducto::TARJETA_CREDITO, CasoProducto::PYME, CasoProducto::PERSONAL]))
?>
<!-- Formulario para productos -->
<form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}">

@if ($errors->any())
<div class="alert alert-danger">
    <strong>{{ __('Please fix the following errors:') }}</strong>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if ($this->panels['info'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-blue">
    <h4 class="mb-0">{{ __('Información del Caso') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.info-caso')
  </div>
</div>
@endif

{{-- Notificación --}}
@if ($this->panels['notificacion'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-gray">
    <h4 class="mb-0">{{ $this->titleNotification }}</h4>
    @include('livewire.casos.partials.davivienda.panels.notificacion-caso')
  </div>
</div>
@endif

{{-- Notificadores y capturadores --}}
@if ($this->panels['notificadoresCapturadores'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-red">
    <h4 class="mb-0">Notificadores y Capturadores</h4>
    @include('livewire.casos.partials.bac.panels.notifiadores-capturadores-caso')
  </div>
</div>
@endif

{{-- Sentencia --}}
@if ($this->panels['sentencia'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-yellow">
    <h4 class="mb-0">{{ __('Sentencia y/o remate') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.sentencia-caso')
  </div>
</div>
@endif

{{-- Arreglo --}}
@if ($this->panels['arreglo'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-green">
    <h4 class="mb-0">{{ __('Arreglo de pago / Tentativa de traspaso') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.arreglo-caso')
  </div>
</div>
@endif

{{-- Aprobación --}}
@if ($this->panels['aprobacion'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-cyan">
    <h4 class="mb-0">{{ __('Aprobación o ejecución') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.aprobacion-caso')
  </div>
</div>
@endif

{{-- Traspaso --}}
@if ($this->panels['traspaso'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-pink">
    <h4 class="mb-0">{{ __('Traspaso') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.traspaso-caso')
  </div>
</div>
@endif

{{-- Terminación --}}
@if ($this->panels['terminacion'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-red">
    <h4 class="mb-0">{{ __('Terminaciòn del proceso') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.terminacion-caso')
  </div>
</div>
@endif

{{-- Levantamiento --}}
@if ($this->panels['levantamiento'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-green">
    <h4 class="mb-0">{{ __('Proceso de levantamiento') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.levantamiento-caso')
  </div>
</div>
@endif

{{-- Facturación --}}
@if ($this->panels['facturacion'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-cyan">
    <h4 class="mb-0">{{ __('Facturaciòn') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.facturacion-caso')
  </div>
</div>
@endif


{{-- Segmento empresas --}}
@if ($this->panels['segmento'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-mint">
    <h4 class="mb-0">{{ __('Segmento empresas de gran tamaño') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.segmento-caso')
  </div>
</div>
@endif

{{-- Denuncia --}}
@if ($this->panels['denuncia'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-coral">
    <h4 class="mb-0">{{ __('Casos con Denuncia de Robo') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.denuncia-caso')
  </div>
</div>
@endif

{{-- Anotaciones --}}
@if ($this->panels['anotaciones'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-beige">
    <h4 class="mb-0">{{ __('Casos con anotaciones de pèrdidas total o traspaso defectuoso') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.anotaciones-caso')
  </div>
</div>
@endif

{{-- Bienes --}}
@if ($this->panels['bienes'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-green">
    <h4 class="mb-0">{{ __('Bienes con gravamen y colisiones') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.bienes-caso')
  </div>
</div>
@endif

{{-- Filtro1 --}}
@if ($this->panels['filtro1'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-red">
    <h4 class="mb-0">{{ __('Panel Remanente filtro1') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.filtro1-caso')
  </div>
</div>
@endif

{{-- Filtro2 --}}
@if ($this->panels['filtro2'])
<div class="card border-0 mb-7">
  <div class="card-body panel-card bg-light-red">
    <h4 class="mb-0">{{ __('Panel Remanente filtro2') }}</h4>
    @include('livewire.casos.partials.davivienda.panels.filtro2-caso')
  </div>
</div>
@endif

<div class="row g-6">
  <div class="col-md">
    <div class="pt-6">
      {{-- Incluye botones de guardar y guardar y cerrar --}}
      @include('livewire.includes.button-saveAndSaveAndClose')

      <!-- Botón Cancel -->
      <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="cancel"
        wire:loading.attr="disabled" wire:target="cancel">
        <span wire:loading.remove wire:target="cancel">
          <span class="fa fa-remove bx-18px me-2"></span>{{ __('Cancel') }}
        </span>
        <span wire:loading wire:target="cancel">
          <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cancelling...') }}
        </span>
      </button>
    </div>
  </div>
</div>

</form>

@script()
<script>

  window.select2Config = {
    contact_id: {
      fireEvent: true,
      wireIgnore: true,
    },
    bank_id: {
      fireEvent: false,
      wireIgnore: false,
    },
    abogado_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    asistente1: {
      fireEvent: false,
      wireIgnore: true,
    },
    asistente2: {
      fireEvent: false,
      wireIgnore: true,
    },
    product_id: {
      fireEvent: true,
      wireIgnore: true,
    },
    proceso_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    currency_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    pexpectativa_recuperacion_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    nmarchamo: {
      fireEvent: false,
      wireIgnore: true,
    },
    nexonerado_cobro: {
      fireEvent: false,
      wireIgnore: true,
    },
    apuesta_posesion: {
      fireEvent: false,
      wireIgnore: true,
    },
    motivo_terminacion: {
      fireEvent: false,
      wireIgnore: true,
    },
    aestado_proceso_general_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    caso_servicio_capturador_id: {
      fireEvent: false,
      wireIgnore: true,
    },
    caso_servicio_notificador_id: {
      fireEvent: false,
      wireIgnore: true,
    },
  };


  $(document).ready(function() {
    Object.entries(select2Config).forEach(([id, config]) => {
      const $select = $('#' + id);
      if (!$select.length) return;

      $select.select2();

      // Default values
      const fireEvent = config.fireEvent ?? false;
      const wireIgnore = config.wireIgnore ?? false;
      //const allowClear = config.allowClear ?? false;
      //const placeholder = config.placeholder ?? 'Seleccione una opción';

      $select.on('change', function() {
        let data = $(this).val();
        $wire.set(id, data, fireEvent);
        $wire.id = data;
        //@this.department_id = data;
        console.log(data);
      });
    });

    window.initSelect2 = () => {
      Object.entries(select2Config).forEach(([id, config]) => {
        const $select = $('#' + id);
        if (!$select.length) return;

        const wireIgnore = config.wireIgnore ?? false;

        if (!wireIgnore) {
          $select.select2();
          console.log("Se reinició el select2 " + id);
        }
      });
    }

    initSelect2();

    Livewire.on('select2', () => {
      setTimeout(() => {
        initSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });
  })
</script>
@endscript
