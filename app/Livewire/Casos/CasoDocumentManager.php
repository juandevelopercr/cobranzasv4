<?php

namespace App\Livewire\Casos;

use App\Models\Caso;
use Illuminate\Database\QueryException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CasoDocumentManager extends Component
{
  use WithFileUploads;

  // Permitir archivos hasta 100 MB
  protected int $maxUploadSize = 1024 * 1024 * 100; // 100 MB

  public $caso_id;
  // Después:
  public $file_by_collection = [];
  public $title_by_collection = [];

  public $documents = [];
  public $editingDocumentId = null;
  public $onlyview;

  public $canview;
  public $cancreate;
  public $canedit;
  public $candelete;
  public $canexport;

  public $collection = 'casos_general_documents'; // general_documents o bank_documents

  protected function rules()
  {
      return [
          "file_by_collection.{$this->collection}" => "required|file|max:{$this->maxUploadSize}|mimes:pdf,doc,docx,xls,xlsx,jpg,png",
          "title_by_collection.{$this->collection}" => 'required|string|max:100',
      ];
  }

  public function messages()
  {
      return [
          "file_by_collection.{$this->collection}.required" => 'Debe seleccionar un archivo.',
          "file_by_collection.{$this->collection}.mimes" => 'Solo se permiten archivos PDF, Word, Excel o imágenes.',
          "file_by_collection.{$this->collection}.max" => 'El archivo es demasiado grande. Máximo 100 MB.',
          "title_by_collection.{$this->collection}.required" => 'Debe indicar un título.',
          "title_by_collection.{$this->collection}.max" => 'El título no puede superar 100 caracteres.',
      ];
  }

  #[On('updateCasoContext')]
  public function handleUpdateContext($data)
  {
    $this->caso_id = $data['caso_id'];
    if (isset($data['collection'])) {
      $this->collection = $data['collection'];
    }
    $this->loadDocuments();
  }

  public function mount($caso_id, $onlyview = false, $canview = true, $cancreate = true, $canedit = true, $candelete = true, $canexport = false, $collection = 'casos_general_documents')
  {
    $this->caso_id = $caso_id;
    $this->onlyview = $onlyview;
    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;
    $this->collection = $collection;
    $this->loadDocuments();
  }

  public function render()
  {
    return view('livewire.casos.caso-document-manager', [
      'canview' => $this->canview,
      'cancreate' => $this->cancreate,
      'canedit' => $this->canedit,
      'candelete' => $this->candelete,
      'canexport' => $this->canexport
    ]);
  }

  public function loadDocuments()
  {
    $caso = Caso::findOrFail($this->caso_id);
    $this->documents = $caso->getMedia($this->collection)
      ->filter(function ($doc) {
        // ✅ solo dejar los que realmente existen en el disco
        return $doc->exists();
      })
      ->map(function ($doc) {
        return [
          'id'        => $doc->id,
          'name'      => $doc->name,
          'size'      => $doc->size,
          'mime_type' => $doc->mime_type,
          'title'     => $doc->getCustomProperty('title', ''),
          'created_at' => $doc->created_at->format('d/m/Y H:i'),
          'path'      => $doc->getPathRelativeToRoot(), // ✅ corregido: usar $doc
          'url'       => $doc->getUrl(),
        ];
      })
      ->toArray();
  }

  public function saveDocument()
  {
      $file = $this->file_by_collection[$this->collection] ?? null;
      $title = $this->title_by_collection[$this->collection] ?? null;

      $this->validate(); // usa rules() dinámicas

      try {
          $caso = Caso::findOrFail($this->caso_id);

          $media = $caso
              ->addMedia($file->getRealPath())
              ->usingName($title)
              ->toMediaCollection($this->collection);

          $media->setCustomProperty('title', $title);
          $media->save();

          $this->dispatch('show-notification', [
              'type' => 'success',
              'message' => __('The record has been updated')
          ]);

          // Limpiar campos de esta colección
          $this->file_by_collection[$this->collection] = null;
          $this->title_by_collection[$this->collection] = null;

          $this->loadDocuments();

      } catch (FileUnacceptableForCollection $e) {
          $this->dispatch('show-notification', [
              'type' => 'error',
              'message' => __('The file type is not accepted') . ' ' . $e->getMessage()
          ]);
      } catch (\Exception $e) {
          $this->dispatch('show-notification', [
              'type' => 'error',
              'message' => __('An error occurred while updating the record') . ' ' . $e->getMessage()
          ]);
      }
  }

  public function editDocument($documentId)
  {
    $caso = Caso::findOrFail($this->caso_id);
    $document = $caso->getMedia($this->collection)->find($documentId);

    if ($document) {
      $this->editingDocumentId = $documentId;
      $this->title_by_collection[$this->collection] = $document->getCustomProperty('title', $document->name);
    }
  }

  public function updateDocument()
  {
    $title = $this->title_by_collection[$this->collection] ?? null;

    if ($this->editingDocumentId && $title) {
      $document = Media::find($this->editingDocumentId);
      $document->name = $title;
      $document->setCustomProperty('title', $title);
      $document->save();

      $this->editingDocumentId = null;
      unset($this->title_by_collection[$this->collection]);
      $this->loadDocuments();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(),
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $document = Media::find($recordId);
      if ($document && $document->delete()) {
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
      }
      $this->loadDocuments();
    } catch (QueryException $e) {
      if ($e->getCode() == '23000') {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The record cannot be deleted because it is related to other data.')
        ]);
      } else {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage()
        ]);
      }
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while deleting the record') . ' ' . $e->getMessage()
      ]);
    }
  }
}
