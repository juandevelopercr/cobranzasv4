<?php

namespace App\Livewire\Modals;

use App\Models\Cabys;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class CabyModal extends Component
{
  use WithFileUploads;
  use WithPagination;

  public $search = '';

  public $active = '';

  public $sortBy = 'id';

  public $sortDir = 'DESC';

  public $perPage = 10;

  public $modalCabysOpen = false; // Controla el estado del modal

  //public $cabys;

  protected $listeners = [
    'openCabysModal' => 'openCabysModal',
  ];

  public function openCabysModal()
  {
    $this->modalCabysOpen = true;
  }

  public function closeCabysModal()
  {
    $this->modalCabysOpen = false;
  }

  public function selectCabyCode($code)
  {
    // Emite un evento para el componente principal
    // Dispatch para el componente principal
    $this->dispatch('cabyCodeSelected', ['code' => $code]);
    $this->modalCabysOpen = false;
  }

  public function render()
  {
    $cabys = Cabys::search($this->search)
      ->when($this->active !== '', function ($query) {
        $query->where('active', $this->active);
      })
      ->whereIn('category5', ['82120', '82130', '82191', '82199', '92919', '83950', '82310'])
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.cabys.caby-modal', compact('cabys'));
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }
}
