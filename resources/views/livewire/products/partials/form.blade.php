<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Product Information') }}</h5>
        <small class="text-body float-end">Default label</small>
      </div>
      <div class="card-body">
        <div class="col-md-12">
          <div class="nav-align-top nav-tabs-shadow mb-6">
            <ul class="nav nav-tabs nav-fill" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link {{ $activeTab == 1 ? 'active' : '' }}" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-home" aria-controls="navs-justified-home" aria-selected="true">
                  <span class="d-none d-sm-block"><i
                      class="tf-icons bx bx-info-circle bx-lg me-1_5 align-text-center"></i>
                    {{ __('General Information') }}
                  </span>
                  <i class="bx bx-info-circle bx-lg d-sm-none"></i>
                </button>
              </li>
              <!-- HONORARIO -->
              @if($this->type_notarial_act == 'HONORARIO')
              <li class="nav-item">
                <button type="button" class="nav-link {{ $activeTab == 2 ? 'active' : '' }}" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-tax" aria-controls="navs-justified-tax" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-coin bx-lg me-1_5 align-text-center"></i>
                    {{ __('Tax') }}
                  </span>
                  <i class="bx bx-coin bx-lg d-sm-none"></i>
                </button>
              </li>
              @endif
              <li class="nav-item">
                <button type="button" class="nav-link {{ $activeTab == 3 ? 'active' : '' }}" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-ht" aria-controls="navs-justified-ht" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-money bx-lg me-1_5 align-text-center"></i>
                    {{ __('Honorarios / Timbres') }}
                    <?php
                    /*
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5 pt-50">3</span>
                    */
                    ?>
                  </span>
                  <i class="bx bx-money bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link {{ $activeTab == 4 ? 'active' : '' }}" role="tab" data-bs-toggle="tab"
                  data-bs-target="#navs-justified-calculo" aria-controls="navs-justified-calculo" aria-selected="false">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-calculator bx-lg me-1_5 align-text-center"></i>
                    {{ __('Revisión de Cálculos') }}
                  </span>
                  <i class="bx bx-calculator bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade {{ $activeTab == 1 ? 'show active' : '' }}" id="navs-justified-home" role="tabpanel">
                @include('livewire.products.partials._form-product')
              </div>
              <!-- HONORARIO -->
             @if($this->type_notarial_act == 'HONORARIO')
              <div class="tab-pane fade {{ $activeTab == 2 ? 'show active' : '' }}" id="navs-justified-tax" role="tabpanel">
                @if($this->recordId)
                  @livewire('product-taxes.product-tax-manager', ['product_id' =>
                  $this->recordId],
                  key('product-tax-' . ($this->recordId ?? uniqid())))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the product') }}
                  </div>
                @endif
              </div>
              @endif
              <div class="tab-pane fade {{ $activeTab == 3 ? 'show active' : '' }}" id="navs-justified-ht" role="tabpanel">
                @if($this->recordId)
                  @livewire('product-honorarios-timbres.product-honorario-timbre-manager', ['product_id' =>
                  $this->recordId, 'tipo'=> $this->type_notarial_act],
                  key('product-honorario-timbre-'.($this->recordId ?? uniqid())))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the product') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade {{ $activeTab == 4 ? 'show active' : '' }}" id="navs-justified-calculo" role="tabpanel">
                  @include('livewire.products.partials._form_desglose')
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<livewire:modals.caby-modal />
