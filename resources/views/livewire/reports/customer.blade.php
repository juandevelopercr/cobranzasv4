<div class="card">
  <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Clientes') }}</h4>
  <div class="card-datatable text-nowrap">
    <div class="dataTables_wrapper dt-bootstrap5 no-footer">
      <form wire:submit.prevent="exportExcel">
        <div class="row g-6">
          <div class="col-md-3"
               x-data="{ loading: false, _s: false }"
               @download-started.window="_s = true"
               @download-ready.window="loading = false; _s = false">
              <label class="form-label invisible">.</label>
              <button type="button" class="btn btn-primary data-submit me-sm-4 me-1 d-block"
                  :disabled="loading"
                  @click="loading = true; _s = false; $wire.exportExcel().then(() => { if (!_s) loading = false })">
                  <span x-show="!loading">
                      <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Exportar') }}
                  </span>
                  <span x-show="loading" x-cloak>
                      <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Generando reporte...') }}
                  </span>
              </button>
          </div>
      </form>
    </div>
  </div>
</div>

@script()
<script>
    (function() {
        Livewire.on('start-download', async ({ url }) => {
        window.dispatchEvent(new CustomEvent('download-started'));
                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Error al generar el reporte');
                    const disposition = response.headers.get('Content-Disposition') ?? '';
                    const filenameMatch = disposition.match(/filename=["']?([^"';\n]+)["']?/i);
                    const filename = filenameMatch ? filenameMatch[1].trim() : 'reporte.xlsx';
                    const blob = await response.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobUrl);
                } catch (e) {
                    console.error(e);
                } finally {
                    window.dispatchEvent(new CustomEvent('download-ready'));
                }
            });
    })();
</script>
@endscript
