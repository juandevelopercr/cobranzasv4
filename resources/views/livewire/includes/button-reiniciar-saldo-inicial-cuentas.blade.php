@php
    $textButton = __('Recalcular Saldo Inicial');
@endphp

 <button type="button"
        class="btn btn-danger btn-sm mx-1 d-flex align-items-center"
        wire:click.prevent="beforerecalcularsaldoinicial"
        wire:loading.attr="disabled"
        wire:target="beforerecalcularsaldoinicial">
    <span wire:loading.remove wire:target="beforerecalcularsaldoinicial">
        <i class="bx bx-sync bx-flip-horizontal me-1"></i>
        <span class="d-none d-sm-inline-block">@if ($textButton) {{ $textButton }} @else {{ __('Recalcular Saldo Inicial') }} @endif
        </span>
    </span>
    <span wire:loading wire:target="beforerecalcularsaldoinicial">
        <i class="spinner-border spinner-border-sm me-2" role="status"></i>
        {{ __('Cargando...') }}
    </span>
</button>
