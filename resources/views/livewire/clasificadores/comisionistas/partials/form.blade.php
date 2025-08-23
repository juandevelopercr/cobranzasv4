<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }}</h6>

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

    <div class="row g-6">
      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="corr">{{ __('Code') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="corr" id="corr"
            class="form-control @error('corr') is-invalid @enderror" placeholder="{{ __('Code') }}">
        </div>
        @error('corr')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="nombre">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-font"></i></span>
          <input type="text" wire:model="nombre" id="nombre"
            class="form-control @error('nombre') is-invalid @enderror" placeholder="{{ __('Name') }}">
        </div>
        @error('nombre')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <label class="form-label" for="impresion">{{ __('Nombre corto') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-font"></i></span>
          <input type="text" wire:model="impresion" id="impresion"
            class="form-control @error('impresion') is-invalid @enderror" placeholder="{{ __('Nombre corto') }}">
        </div>
        @error('impresion')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-8">
          <input type="checkbox" class="form-check-input" id="active" wire:model.defer="active" {{ $active==1
            ? 'checked' : '' }} />

          <label for="future-billing" class="switch-label">{{ __('Active') }}</label>
        </div>
      </div>
    </div>

    <div class="row g-6">
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
  </form>
</div>
