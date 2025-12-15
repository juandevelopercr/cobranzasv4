<button wire:click="exportVisible" type="button" class="btn btn-sm mx-1 btn-primary d-flex align-items-center gap-1"
  wire:loading.attr="disabled" wire:target="exportVisible">
  <span wire:loading.remove wire:target="exportVisible">
    <i class="bx bx-export"></i>
    <span class="d-none d-sm-inline-block">{{ __('Exportar') }}</span>
  </span>
  <span wire:loading wire:target="exportVisible">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Loading...') }}
  </span>
</button>
