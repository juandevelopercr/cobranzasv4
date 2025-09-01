<?php
use App\Models\User;
use App\Models\CasoEstado;
?>
<!-- Formulario para productos -->
<form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}">
<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-primary">1. {{ __('Información del Caso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.info-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-secondary text-white">2. {{ __('Notificación - Public Edicto') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.notificacion-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-warning text-white">3. {{ __('Sentencia y/o remate') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.sentencia-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-success text-white">4. {{ __('Arreglo de pago / Tentativa de traspaso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.arreglo-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-info text-white">5. {{ __('Aprobación o ejecución') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.aprobacion-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-primary text-white">6. {{ __('Traspaso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.traspaso-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-danger text-white">7. {{ __('Terminaciòn del proceso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.terminacion-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-success text-white">8. {{ __('Proceso de levantamiento') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.levantamiento-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-primary text-white">9. {{ __('Facturaciòn') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.facturacion-caso')
      </div>
    </div>
  </div>
</div>


<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-secundary text-white">10. {{ __('Segmento empresas de gran tamaño') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.segmento-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-info text-white">11. {{ __('Casos con Denuncia de Robo') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.denuncia-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-black text-white">12. {{ __('Casos con anotaciones de pèrdidas total o traspaso defectuoso') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.anotaciones-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-info text-white">13. {{ __('Bienes con gravamen y colisiones') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.bienes-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-danger text-white">14. {{ __('Panel Remanente filtro1') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.filtro1-caso')
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-md">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-0"><span class="badge bg-warning text-white">15. {{ __('Panel Remanente filtro2') }}</span></h4><br>
        @include('livewire.casos.partials.scotiabank.panels.filtro2-caso')
      </div>
    </div>
  </div>
</div>




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
      fireEvent: false,
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
