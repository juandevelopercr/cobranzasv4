@php
    $textButton = __('Recalcular Saldo Final');
@endphp

 <button type="button"
        class="btn btn-danger btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="beforerecalcularcuentas"
        wire:loading.attr="disabled"
        wire:target="beforerecalcularcuentas">
    <span wire:loading.remove wire:target="beforerecalcularcuentas">
        <i class="bx bx-sync bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Recalcular Saldo Final') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="beforerecalcularcuentas">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
