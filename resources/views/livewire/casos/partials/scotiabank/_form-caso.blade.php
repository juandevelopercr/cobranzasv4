<?php
use App\Models\User;
use App\Models\CasoEstado;
use App\Models\CasoProducto;
$show = false;
//@if (in_array($this->product_id, [CasoProducto::TARJETA_CREDITO, CasoProducto::PYME, CasoProducto::PERSONAL]))
?>
<!-- Formulario para productos -->
<form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}">

{{-- Información del caso (siempre) --}}
@if ($this->panels['info'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-primary">{{ __('Información del Caso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.info-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Notificación --}}
@if ($this->panels['notificacion'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-secondary text-white">{{ $this->titleNotification }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.notificacion-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Sentencia --}}
@if ($this->panels['sentencia'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-warning text-white">{{ __('Sentencia y/o remate') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.sentencia-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Arreglo --}}
@if ($this->panels['arreglo'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-success text-white">{{ __('Arreglo de pago / Tentativa de traspaso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.arreglo-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Aprobación --}}
@if ($this->panels['aprobacion'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-info text-white">{{ __('Aprobación o ejecución') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.aprobacion-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Traspaso --}}
@if ($this->panels['traspaso'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-primary text-white">{{ __('Traspaso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.traspaso-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Terminación --}}
@if ($this->panels['terminacion'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-danger text-white">{{ __('Terminaciòn del proceso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.terminacion-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Levantamiento --}}
@if ($this->panels['levantamiento'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-success text-white">{{ __('Proceso de levantamiento') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.levantamiento-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Facturación --}}
@if ($this->panels['facturacion'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-primary text-white">{{ __('Facturaciòn') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.facturacion-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Segmento empresas --}}
@if ($this->panels['segmento'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-secundary text-white">{{ __('Segmento empresas de gran tamaño') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.segmento-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Denuncia --}}
@if ($this->panels['denuncia'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-info text-white">{{ __('Casos con Denuncia de Robo') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.denuncia-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Anotaciones --}}
@if ($this->panels['anotaciones'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-black text-white">{{ __('Casos con anotaciones de pèrdidas total o traspaso defectuoso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.anotaciones-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Bienes --}}
@if ($this->panels['bienes'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-info text-white">{{ __('Bienes con gravamen y colisiones') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.bienes-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Filtro1 --}}
@if ($this->panels['filtro1'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-danger text-white">{{ __('Panel Remanente filtro1') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.filtro1-caso')
      </div>
    </div>
  </div>
</div>
@endif

{{-- Filtro2 --}}
@if ($this->panels['filtro2'])
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-warning text-white">{{ __('Panel Remanente filtro2') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.filtro2-caso')
      </div>
    </div>
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
    }
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
