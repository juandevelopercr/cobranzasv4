<?php
use App\Models\User;
?>
<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->

<div class="row">
  <div class="col-xl">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          @if ($this->proforma_no)
          {{ __('Update Proforma') }}: No. {{ $this->proforma_no }}
          @else
          {{ __('Create Proforma') }}
          @endif
        </h5>
        <small class="text-body float-end">Default label</small>
      </div>
      <div class="card-body">
        <div class="col-md-12">
          <div class="nav-align-top nav-tabs-shadow mb-6" x-data="{ activeTab: 'invoice' }">
            <ul class="nav nav-tabs nav-fill" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'invoice' }" role="tab"
                  @click="activeTab = 'invoice'">
                  <span class="d-none d-sm-block"><i
                      class="tf-icons bx bx-info-circle bx-lg me-1_5 align-text-center"></i>
                    {{ __('General Information') }}
                  </span>
                  <i class="bx bx-info-circle bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'product' }" role="tab"
                  @click="activeTab = 'product'">
                  <span class="d-none d-sm-block">
                    <i class="tf-icons bx bx-cog bx-lg me-1_5 align-text-center"></i>
                    {{ __('Services') }}
                    <?php
                    /*
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5 pt-50">3</span>
                    */
                    ?>
                  </span>
                  <i class="bx bx-cog bx-lg d-sm-none"></i>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'charges' }" role="tab"
                  @click="activeTab = 'charges'">
                  <span class="d-none d-sm-block"><i class="tf-icons bx bx-dollar bx-lg me-1_5 align-text-center"></i>
                    {{ __('Other Charge') }}
                  </span>
                  <i class="bx bx-dollar bx-lg d-sm-none"></i>
                </button>
              </li>
              @if (auth()->user()->hasAnyRole(User::ROLES_ALL_BANKS))
              <li class="nav-item">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'comisiones' }" role="tab"
                  @click="activeTab = 'comisiones'">
                  <span class="d-none d-sm-block"><i class="tf-icons bx bx-chart bx-lg me-1_5 align-text-center"></i>
                    {{ __('Cost Centers and Commissions') }}
                  </span>
                  <i class="bx bx-chart bx-lg d-sm-none"></i>
                </button>
              </li>
              @endif
              <li class="nav-item">
                <button type="button" class="nav-link" :class="{ 'active': activeTab === 'documentos' }" role="tab"
                  @click="activeTab = 'documentos'">
                  <span class="d-none d-sm-block">
                  <i class="tf-icons bx bx-file bx-lg me-1_5 align-text-center"></i>
                    {{ __('Attached Documents') }}
                  </span>
                  <i class="bx bx-file bx-lg d-sm-none"></i>
                </button>
              </li>
            </ul>


            <div class="tab-content">
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'invoice' }" id="navs-justified-home" role="tabpanel">
                    @include('livewire.transactions.partials._form-proforma')
              </div>
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'product' }" id="navs-justified-services" role="tabpanel">
                @if($this->recordId)
                  @livewire('transactions-lines.transaction-line-manager', [
                    'transaction_id' => $this->recordId,
                    'canview'   => auth()->user()->can('view-lineas-proformas'),
                    'cancreate' => auth()->user()->can('create-lineas-proformas'),
                    'canedit'   => auth()->user()->can('edit-lineas-proformas'),
                    'candelete' => auth()->user()->can('delete-lineas-proformas'),
                    'canexport' => auth()->user()->can('export-lineas-proformas')
                  ], key('line-manager'))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the proforma') }}
                  </div>
                @endif
              </div>
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'charges' }" id="navs-justified-charges" role="tabpanel">
                @if($this->recordId)
                  @livewire('transactions-charges.transaction-charge-manager', [
                    'transaction_id' => $this->recordId,
                    'canview'   => auth()->user()->can('view-cargos-proformas'),
                    'cancreate' => auth()->user()->can('create-cargos-proformas'),
                    'canedit'   => auth()->user()->can('edit-cargos-proformas'),
                    'candelete' => auth()->user()->can('delete-cargos-proformas'),
                    'canexport' => auth()->user()->can('export-cargos-proformas'),
                  ], key('charge-manager'))
                @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-xs bx-wallet"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the proforma') }}
                  </div>
                @endif
              </div>
              @if (auth()->user()->hasAnyRole(User::ROLES_ALL_BANKS))
                <div class="tab-pane fade" :class="{ 'show active': activeTab === 'comisiones' }" id="navs-justified-comisiones" role="tabpanel">
                  @if($this->recordId)
                    @livewire('transactions-commissions.transaction-commission-manager', [
                      'transaction_id' => $this->recordId,
                      'canview'   => auth()->user()->can('view-comision-proformas'),
                      'cancreate' => auth()->user()->can('create-comision-proformas'),
                      'canedit'   => auth()->user()->can('edit-comision-proformas'),
                      'candelete' => auth()->user()->can('delete-comision-proformas'),
                      'canexport' => auth()->user()->can('export-comision-proformas'),
                    ], key('commission-manager'))
                  @else
                    <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                      <span class="alert-icon rounded-circle">
                        <i class="bx bx-xs bx-wallet"></i>
                      </span>
                      {{ __('Information will be displayed here after you have created the proforma') }}
                    </div>
                  @endif
                </div>
              @endif
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'documentos' }" id="navs-justified-documentos" role="tabpanel">

                @if($this->recordId)
                  @livewire('transactions.documents-manager', [
                    'transaction_id' => $this->recordId,
                    'onlyview' => false,
                    'canview'   => auth()->user()->can('view-documento-proformas'),
                    'cancreate' => auth()->user()->can('create-documento-proformas'),
                    'canedit'   => auth()->user()->can('edit-documento-proformas'),
                    'candelete' => auth()->user()->can('delete-documento-proformas'),
                    'canexport' => auth()->user()->can('export-documento-proformas'),

                  ])
                  @else
                  <div class="alert alert-solid-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon rounded-circle">
                      <i class="bx bx-file bx-lg d-sm-none"></i>
                    </span>
                    {{ __('Information will be displayed here after you have created the proforma') }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@if($modalCustomerOpen)
<div id="customer-modal" class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Select Customer') }}</h5>
        <button type="button" class="btn-close" wire:click="closeCustomerModal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @livewire('contacts.contact-manager', [
                    'enabledSelectedValue' => true,
                    'type' => 'customer'
                  ],
                  key('contact-manager'.$this->recordId))
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="closeCustomerModal">
          {{ __('Close') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endif
