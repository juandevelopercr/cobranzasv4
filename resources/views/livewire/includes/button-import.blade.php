<button wire:click="showImport" type="button"
  class="btn btn-success btn-sm mx-1 d-flex align-items-center"
  wire:loading.attr="disabled" wire:target="showImport">
  <span wire:loading.remove wire:target="showImport">
    <i class="bx bx-import bx-flip-horizontal"></i>
    <span class="d-none d-sm-inline-block">{{ __('Importar') }}</span>
  </span>
  <span wire:loading wire:target="showImport">
    <i class="spinner-border spinner-border-sm me-2" role="status"></i>
    {{ __('Loading...') }}
  </span>
</button>
