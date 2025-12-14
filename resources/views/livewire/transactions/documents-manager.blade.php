@php
    function getFileIconClass($mimeType)
    {
        $icons = [
            'application/pdf' => 'bxs-file-pdf text-danger',
            'application/vnd.ms-excel' => 'bxs-file text-success',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'bxs-file text-success',
            'application/msword' => 'bxs-file-doc text-primary',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'bxs-file-doc text-primary',
            'image/jpeg' => 'bxs-image text-warning',
            'image/png' => 'bxs-image text-warning',
            'text/plain' => 'bxs-file-txt text-secondary',
        ];
        return $icons[$mimeType] ?? 'bxs-file text-secondary';
    }
@endphp

<div class="p-4" x-data="{ isDropping: false, isUploading: false }">
    <!-- Upload Section -->
    @if ($this->onlyview == false && $cancreate)
        <div class="card mb-2 shadow-sm">
            <div class="card-body">
                <form wire:submit.prevent="saveDocument">
                    <div class="row g-4">
                        <!-- Drag & Drop Zone -->
                        <div class="col-md-5">
                            <div class="border-2 border-dashed rounded p-4 d-flex flex-column justify-content-center align-items-center text-center cursor-pointer transition-all position-relative"
                                :class="isDropping ? 'border-primary bg-primary-subtle' : 'border-secondary-subtle bg-light'"
                                style="min-height: 200px; transition: all 0.2s ease;"
                                x-on:dragover.prevent="isDropping = true" x-on:dragleave.prevent="isDropping = false"
                                x-on:drop.prevent="
                                    isDropping = false;
                                    isUploading = true;
                                    $refs.fileInput.files = $event.dataTransfer.files;
                                    $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                "
                                @click="$refs.fileInput.click()">
                                <input type="file" wire:model="file" x-ref="fileInput" class="d-none"
                                    id="file_upload" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">

                                <!-- Loading State -->
                                <div wire:loading wire:target="file"
                                    class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-75 rounded"
                                    style="z-index: 10;">
                                    <div class="d-flex flex-column justify-content-center align-items-center h-100">
                                        <div class="spinner-border text-primary mb-2" role="status"></div>
                                        <span class="text-primary fw-semibold">{{ __('Procesando archivo...') }}</span>
                                    </div>
                                </div>

                                <!-- Preview / Instruction -->
                                @if ($file)
                                    @php
                                        $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
                                    @endphp
                                    <div
                                        class="d-flex flex-column align-items-center animate__animated animate__fadeIn">
                                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ $file->temporaryUrl() }}"
                                                class="img-fluid rounded shadow-sm mb-3"
                                                style="max-height: 120px; object-fit: cover;" alt="Preview">
                                        @else
                                            <i class='bx {{ getFileIconClass($file->getMimeType()) }}'
                                                style="font-size: 4rem;"></i>
                                        @endif
                                        <div class="mt-2 text-dark fw-bold text-truncate" style="max-width: 200px;">
                                            {{ $file->getClientOriginalName() }}
                                        </div>
                                        <span
                                            class="badge bg-label-primary mt-1">{{ number_format($file->getSize() / 1024, 2) }}
                                            KB</span>
                                        <button type="button" class="btn btn-sm btn-label-danger mt-3"
                                            wire:click="$set('file', null); $refs.fileInput.value = ''" @click.stop>
                                            <i class="bx bx-trash me-1"></i> {{ __('Eliminar') }}
                                        </button>
                                    </div>
                                @else
                                    <div class="d-flex flex-column align-items-center text-muted">
                                        <div class="mb-3 p-3 rounded-circle bg-label-primary">
                                            <i class="bx bx-cloud-upload bx-lg text-primary"></i>
                                        </div>
                                        <h5 class="mb-1 text-dark fw-semibold">
                                            {{ __('Arrastre y suelte su archivo aquí') }}</h5>
                                        <p class="mb-3 text-secondary small">{{ __('o haga clic para buscar') }}</p>
                                        <span class="text-xs text-muted">PDF, Word, Excel, Imagenes (Max 100MB)</span>
                                    </div>
                                @endif
                            </div>
                            @error('file')
                                <div class="text-danger mt-2 small"><i
                                        class="bx bx-error-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Details -->
                        <div class="col-md-7 d-flex flex-column justify-content-center">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('Título del Documento') }} <span
                                        class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-rename"></i></span>
                                    <input type="text" wire:model="title" class="form-control"
                                        placeholder="{{ __('ej., Contrato Firmado, Factura #123...') }}">
                                </div>
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch custom-option-basic">
                                    <input class="form-check-input" type="checkbox" wire:model="attach_to_email"
                                        id="attach_switch" checked>
                                    <label class="form-check-label d-flex align-items-start" for="attach_switch">
                                        <i class="bx bx-envelope me-2"></i>
                                        <div>
                                            <span class="fw-semibold">{{ __('Adjuntar al Correo') }}</span>
                                            <div class="text-muted small">Si se marca, este documento se enviará al
                                                cliente.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled"
                                    wire:target="saveDocument">
                                    <span wire:loading.remove wire:target="saveDocument"
                                        class="d-flex align-items-center">
                                        <i class="bx bx-save me-2"></i>{{ __('Subir y Guardar') }}
                                    </span>
                                    <span wire:loading wire:target="saveDocument">
                                        <span class="d-flex align-items-center">
                                            <i
                                                class="spinner-border spinner-border-sm me-2"></i>{{ __('Guardando...') }}
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Documents List -->
    <div class="row g-4 mt-2">
        @forelse($documents as $index => $document)
            <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                <div class="card h-100 shadow-sm hover-shadow transition-all border position-relative">
                    <div class="card-body p-3 d-flex align-items-start">
                        <!-- Icon -->
                        <div class="me-3 mt-1">
                            <div
                                class="avatar avatar-md rounded bg-label-secondary p-2 d-flex align-items-center justify-content-center">
                                <i class='bx {{ getFileIconClass($document['mime_type']) }}'
                                    style="font-size: 1.8rem;"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1 overflow-hidden">
                            @if ($canedit && $editingDocumentId === $document['id'])
                                <!-- Edit Mode -->
                                <div class="d-flex flex-column gap-2 animate__animated animate__fadeIn">
                                    <input type="text" wire:model="title" class="form-control form-control-sm"
                                        placeholder="Title">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" wire:model="attach_to_email"
                                            id="edit_attach_{{ $document['id'] }}">
                                        <label class="form-check-label"
                                            for="edit_attach_{{ $document['id'] }}">{{ __('Adjuntar al Correo') }}</label>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button wire:click="updateDocument" class="btn btn-xs btn-primary"
                                            wire:loading.attr="disabled" wire:target="updateDocument">
                                            <i class="bx bx-check" wire:loading.remove
                                                wire:target="updateDocument"></i>
                                            <i class="spinner-border spinner-border-sm" wire:loading
                                                wire:target="updateDocument"></i>
                                        </button>
                                        <button wire:click="$set('editingDocumentId', null)"
                                            class="btn btn-xs btn-outline-secondary"><i class="bx bx-x"></i></button>
                                    </div>
                                </div>
                            @else
                                <!-- View Mode -->
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 text-truncate text-dark fw-bold" title="{{ $document['name'] }}">
                                        {{ $document['name'] }}
                                    </h6>

                                    <!-- Options Dropdown -->
                                    <div class="dropdown ms-2">
                                        <button class="btn p-0" type="button" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item" href="{{ $document['url'] }}"
                                                    target="_blank"><i
                                                        class="bx bx-download me-2"></i>{{ __('Descargar') }}</a></li>
                                            @if ($this->onlyview == false)
                                                @if ($canedit)
                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                            wire:click="editDocument({{ $document['id'] }})"><i
                                                                class="bx bx-edit me-2"></i>{{ __('Editar Detalles') }}</a>
                                                    </li>
                                                @endif
                                                @if ($candelete)
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger"
                                                            href="javascript:void(0);"
                                                            wire:click.prevent="confirmarAccion({{ $document['id'] }}, 'delete', '{{ __('¿Eliminar Documento?') }}', '{{ __('Esta acción no se puede deshacer.') }}', '{{ __('Sí, Eliminar') }}')">
                                                            <i class="bx bx-trash me-2"></i>{{ __('Eliminar') }}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="text-muted small mb-2 d-flex flex-column">
                                    <span><i class="bx bx-file-blank me-1"></i>
                                        {{ isset($document['title']) && $document['title'] ? $document['title'] : $document['name'] }}</span>
                                    <span class="mt-1"><i class="bx bx-time-five me-1"></i>
                                        {{ $document['created_at'] }} &bull;
                                        {{ number_format($document['size'] / 1024, 2) }} KB</span>
                                </div>

                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span
                                        class="badge cursor-pointer {{ !empty($document['attach_to_email']) ? 'bg-label-success' : 'bg-label-secondary' }}"
                                        wire:click="toggleAttachToEmail({{ $index }})"
                                        data-bs-toggle="tooltip"
                                        title="{{ __('Clic para cambiar estado de adjunto') }}">
                                        <i
                                            class="bx {{ !empty($document['attach_to_email']) ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                        {{ !empty($document['attach_to_email']) ? __('Adjunto al Correo') : __('No Adjunto') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 mt-4 text-center">
                <div class="d-flex flex-column align-items-center">
                    <div class="mb-3 p-3 rounded-circle bg-light">
                        <i class="bx bx-folder-open bx-lg text-muted"></i>
                    </div>
                    <h5 class="text-muted">{{ __('No se encontraron documentos') }}</h5>
                    <p class="text-secondary small">{{ __('Suba documentos para verlos aquí.') }}</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
