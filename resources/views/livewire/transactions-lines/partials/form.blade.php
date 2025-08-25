@php
  use App\Models\User;
@endphp
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
      <!-- Product -->
      <div class="col-md-4 select2-primary fv-plugins-icon-container"
          x-data="select2Livewire({
            wireModelName: 'product_id',
            postUpdate: true
          })"
          x-init="init($refs.select)"
          wire:ignore>
        <label class="form-label" for="product_id">{{ __('Product') }}</label>
        <select x-ref="select" id="product_id"
                class="select2 form-select @error('product_id') is-invalid @enderror">
          <option value="">{{ __('Seleccione...') }}</option>
          @foreach ($this->products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
          @endforeach
        </select>
        @error('product_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="codigocabys">{{ __('Caby Code') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-barcode"></i></span>
          <input type="text" wire:model="codigocabys" id="codigocabys"
            class="form-control @error('codigocabys') is-invalid @enderror" placeholder="{{ __('Caby Code') }}" readonly>
          <button type="button" class="btn btn-primary" wire:click="$dispatch('openCabysModal')">
            {{ __('Select') }}
          </button>
        </div>
        @error('codigocabys')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- price -->
      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="price">{{ __('Price') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $price ?? '' }}',
            wireModelName: 'price',
            postUpdate: false,
            decimalScale: 2,
            allowNegative: true,
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('price', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            {{--  <span class="input-group-text"><i class="bx bx-dollar"></i></span> --}}
            <input type="text" id="price" x-ref="cleaveInput" wire:ignore class="form-control js-input-price" />
          </div>
        </div>
        @error('price')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- quantity -->
      <div class="col-md-2 fv-plugins-icon-container">
        <label class="form-label" for="quantity">{{ __('Quantity') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $quantity ?? '' }}',
            wireModelName: 'quantity',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            watchProperty: 'quantity',
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('quantity', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <span class="input-group-text"><i class="bx bx-hash"></i></span>
            <input type="text" id="quantity" x-ref="cleaveInput" wire:ignore class="form-control js-input-quantity" />
          </div>
        </div>
        @error('quantity')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <!-- porcientoDescuento -->
      <div class="col-md-2 fv-plugins-icon-container">
        <label class="form-label" for="porcientoDescuento">{{ __('Porciento de descuento') }}</label>
        <div
          x-data="cleaveLivewire({
            initialValue: '{{ $porcientoDescuento ?? '' }}',
            wireModelName: 'porcientoDescuento',
            postUpdate: false,
            decimalScale: 0,
            allowNegative: true,
            watchProperty: 'porcientoDescuento',
            rawValueCallback: (val) => {
              //console.log('Callback personalizado:', val);
              // lógica extra aquí si deseas
              const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
              if (component) {
                component.set('porcientoDescuento', val); // <- Esto envía el valor sin comas
              }
            }
          })"
          x-init="init($refs.cleaveInput)"
        >
          <div class="input-group input-group-merge has-validation">
            <input type="text" id="porcientoDescuento" x-ref="cleaveInput" wire:ignore class="form-control js-input-descuento"/>
          </div>
        </div>
        @error('porcientoDescuento')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-7 fv-plugins-icon-container">
        <label class="form-label" for="detail">{{ __('Detail of the Notarial Act') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span id="spandetail" class="input-group-text"><i class="bx bx-receipt"></i></span>
          <input type="text" wire:model="detail" name="detail" id="detail"
            class="form-control @error('detail') is-invalid @enderror"
            placeholder="{{ __('Detail of the Notarial Act') }}" aria-label="{{ __('Detail of the Notarial Act') }}"
            aria-describedby="spandetail">
        </div>
        @error('detail')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>

    @include('livewire.transactions-lines.partials._form-taxes')

    @if (auth()->user()->hasAnyRole(User::ROLES_ALL_BANKS))
      @include('livewire.transactions-lines.partials._form-discounts')
    @endif

    <div class="pt-6 pb-0">
      <h6>3. {{ __('Desglose del acto notarial') }}</h6>
      @if ($this->degloseHtml)
        {!! $this->degloseHtml !!}
      @else
       <div class="col-md-12 d-flex align-items-center">
            <div class="alert alert-primary mb-0 w-100">
                <i class="fas fa-info-circle me-2"></i>
                Sin datos que mostrar
            </div>
        </div>
      @endif

    </div>

    <!-- Resumen Final (siempre visible) -->
    @php
    /*
    <div class="mt-4 text-end">
      <h6>{{ __('Summary') }}</h6>
      <p>{{ __('Subtotal') }}: ${{ number_format($subtotal, 2) }}</p>
      <p>{{ __('Taxes') }}: ${{ number_format($totalTaxes, 2) }}</p>
      <h5>{{ __('Total') }}: ${{ number_format($finalTotal, 2) }}</h5>
    </div>
    */
    @endphp


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

@php
/*
@script()
<script>
  Livewire.on('reinitControls', postId => {
      jQuery(document).ready(function () {
         // Función para inicializar Select2
        function initializeSelect2() {
            $('.select2').select2();

            // Sincronización con Livewire
            $('#product_id').on('change', function () {
                @this.set('product_id', $(this).val());
            });
        }

        // Inicializar Select2 al cargar la página
        initializeSelect2();

      });
  });
</script>
@endscript
*/
@endphp
