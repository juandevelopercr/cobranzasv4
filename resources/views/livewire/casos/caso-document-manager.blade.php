@php
  $fileInput = $file_by_collection[$collection] ?? null;
  $titleInput = $title_by_collection[$collection] ?? '';
@endphp

<div>
  <!-- File Upload Form -->
  <h5 class="mb-4">
    {{ $collection === 'casos_bank_documents' ? __('Bank Documents') : __('General Documents') }}
  </h5>
  @if ($this->onlyview == false && $cancreate)
  <form wire:submit.prevent="saveDocument" enctype="multipart/form-data">
    <div class="border-bottom pb-4">
      <div class="row g-3 align-items-center">

        <!-- Vista Previa del Archivo -->
        <div class="col-md-3 text-center">
          @if ($fileInput && $fileInput->getClientOriginalName() !== 'livewire-tmp')
            @php
              $extension = strtolower(pathinfo($fileInput->getClientOriginalName(), PATHINFO_EXTENSION));
            @endphp

            @if (in_array($extension, ['png', 'jpg', 'jpeg']))
              <img src="{{ $fileInput->temporaryUrl() }}" class="d-block w-px-100 h-px-100 rounded" alt="Preview">
            @elseif ($extension === 'pdf')
              <div class="d-flex flex-column align-items-center">
                <i class="bx bxs-file-pdf bx-lg text-danger"></i>
                <p class="mt-2">{{ $fileInput->getClientOriginalName() }}</p>
                <a href="{{ $fileInput->temporaryUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">
                  <i class="bx bx-show"></i> {{ __('View PDF') }}
                </a>
              </div>
            @elseif (in_array($extension, ['xls', 'xlsx']))
              <div class="d-flex flex-column align-items-center">
                <i class="bx bxs-file-excel bx-lg text-success"></i>
                <p class="mt-2">{{ $fileInput->getClientOriginalName() }}</p>
                <a href="{{ $fileInput->temporaryUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">
                  <i class="bx bx-download"></i> {{ __('Download Excel') }}
                </a>
              </div>
            @elseif (in_array($extension, ['doc', 'docx']))
              <div class="d-flex flex-column align-items-center">
                <i class="bx bxs-file-word bx-lg text-primary"></i>
                <p class="mt-2">{{ $fileInput->getClientOriginalName() }}</p>
                <a href="{{ $fileInput->temporaryUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">
                  <i class="bx bx-download"></i> {{ __('Download Word') }}
                </a>
              </div>
            @else
              <p class="text-danger">{{ __('Unsupported file type') }}: {{ $extension }}</p>
            @endif
          @else
            <div class="d-flex justify-content-center align-items-center w-px-100 h-px-100 border rounded bg-light">
              <i class="bx bx-file-blank bx-lg text-muted"></i>
            </div>
          @endif
        </div>

        <!-- Seleccionar Archivo -->
        <div class="col-md-5">
          <div class="d-flex align-items-center">
            <label for="file-{{ $collection }}" class="btn btn-primary me-3 d-flex align-items-center">
              <i class="bx bx-upload bx-sm me-2"></i>
              <span>{{ __('Select') }}</span>
              <input type="file" wire:model="file_by_collection.{{ $collection }}" id="file-{{ $collection }}" hidden accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
            </label>
            <div class="col-md-8 mt-1">
              <input type="text" wire:model="title_by_collection.{{ $collection }}" class="form-control" placeholder="{{ __('Title') }}">
              @error('title_by_collection.' . $collection) <span class="text-danger">{{ $message }}</span> @enderror
            </div>
          </div>
          @error('file_by_collection.' . $collection) <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- Botón Guardar -->
        <div class="col-md-2 d-flex align-items-center pt-3">
          <button type="submit" class="btn btn-primary d-flex align-items-center" wire:loading.attr="disabled" wire:target="saveDocument">
            <span wire:loading.remove wire:target="saveDocument">
              <i class="bx bx-save bx-sm me-2"></i>
              {{ __('Save') }}
            </span>
            <span wire:loading wire:target="saveDocument">
              <i class="spinner-border spinner-border-sm me-2" role="status"></i>
              {{ __('Saving...') }}
            </span>
          </button>
        </div>

        <!-- Loading Spinner -->
        <div wire:loading wire:target="file_by_collection.{{ $collection }}" class="col-12 text-center mt-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('Uploading...') }}</span>
          </div>
          <p>{{ __('Uploading file... Please wait') }}</p>
        </div>
      </div>
    </div>
  </form>
  @endif

  <!-- Document List -->
  <div class="row mt-5">
    @foreach($documents as $document)
      @php
      $fileExists = \Illuminate\Support\Facades\Storage::disk($document['disk'] ?? 'public')
                    ->exists($document['path'] ?? null);
      @endphp
      @php
      /*
      @if($fileExists)
      */
      @endphp
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">
              <i class="bx bx-file {{ getIcon($document['mime_type']) }}" style="font-size: 2rem;"></i>
              {{ $document['name'] }}
            </h5>
            <p class="card-text">
              {{ __('Size') }}: {{ number_format($document['size'] / 1024, 2) }} KB <br>
              {{ __('Created at') }}: {{ \Carbon\Carbon::createFromFormat('d/m/Y H:i', $document['created_at'])->format('d/m/Y H:i') }}
            </p>

            @if($canedit && $editingDocumentId === $document['id'])
              <div class="mt-4">
                <div class="d-flex align-items-center gap-3">
                  <input type="text" wire:model="title_by_collection.{{ $collection }}" class="form-control" placeholder="{{ __('Document Title') }}">
                  @error('title_by_collection.' . $collection) <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="mt-3 d-flex gap-2">
                  <button wire:click="updateDocument" class="btn btn-primary d-flex align-items-center" wire:loading.attr="disabled" wire:target="updateDocument">
                    <span wire:loading.remove wire:target="updateDocument">
                      <i class="bx bx-save bx-sm me-2"></i>{{ __('Save') }}
                    </span>
                    <span wire:loading wire:target="updateDocument">
                      <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Saving...') }}
                    </span>
                  </button>
                  <button wire:click="$set('editingDocumentId', null)" class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fa fa-remove bx-sm me-2"></i>{{ __('Cancel') }}
                  </button>
                </div>
              </div>
            @elseif ($this->onlyview == false)
              @if ($canedit)
                <button wire:click="editDocument({{ $document['id'] }})" class="btn btn-icon item-edit" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                  <i class="bx bx-edit bx-md"></i>
                </button>
              @endif
              <a href="{{ $document['url'] }}" target="_blank" class="btn btn-icon" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                <i class="bx bxs-download bx-md"></i>
              </a>
              @if ($candelete)
                <button wire:click.prevent="confirmarAccion({{ $document['id'] }}, 'delete', '{{ __('Are you sure you want to delete this record') }} ?', '{{ __('After confirmation, the record will be deleted') }}', '{{ __('Yes, proceed') }}')" class="btn btn-icon item-trash text-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                  <i class="bx bx-trash bx-md"></i>
                </button>
              @endif
            @endif
          </div>
        </div>
      </div>
      @php
      /*
      @endif
      */
      @endphp
    @endforeach
  </div>
</div>
