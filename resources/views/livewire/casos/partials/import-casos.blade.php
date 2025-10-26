<div class="card mb-6">
    <form wire:submit.prevent="importar" class="p-4 bg-white rounded shadow-md">
        @if($expectedColumns)
            <div class="row">
                <strong>Columnas esperadas:</strong>
                @foreach($expectedColumns as $index => $config)
                    <div class="col-md-3">
                        <strong>{{ $this->excelColumnLetter($loop->index) }}</strong> - {{ $index }}
                    </div>
                @endforeach
            </div>
        @endif
        <br><br>
        <div class="row g-6">
          <div class="mb-3">
              <label class="form-label">Archivo Excel (.xlsx / .xls)</label>
              <input type="file" wire:model="archivo" class="form-control" />
              @error('archivo') <span class="text-danger">{!! $message !!}</span> @enderror
          </div>
        </div>

        <div class="row g-6">
            <div class="col-md">
                <div class="pt-6">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <!-- Icono de subida / upload -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload mr-2" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                          <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                          <path d="M12 3v12" />
                          <path d="M9 6l3 -3l3 3" />
                        </svg>
                        <span wire:loading.remove>Importar</span>
                        <span wire:loading>Procesando...</span>
                    </button>

                    &nbsp;&nbsp;
                    <button type="button" wire:click="descargarPlantilla()" class="btn btn-secondary">
                        <!-- Icono de descarga -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download mr-2" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                          <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                          <path d="M12 3v14" />
                          <path d="M9 10l3 3l3 -3" />
                        </svg>
                        Descargar plantilla BAC
                    </button>

                    &nbsp;&nbsp;
                    <button type="button" wire:click="cancelarImportacion" class="btn btn-danger">
                        <!-- Icono de cancel -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x mr-2" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                          <line x1="18" y1="6" x2="6" y2="18" />
                          <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

    </form>

    @if($this->message)
        <div class="alert alert-{{ $tipoMessage }} mt-4">
            {!! $message !!}
        </div>
    @endif
</div>
