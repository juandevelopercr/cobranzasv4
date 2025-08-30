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
        <h6>1. {{ __('1- Información del Caso') }}</h6>
        @include('livewire.casos.partials.scotiabank.panels.info-caso')
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
