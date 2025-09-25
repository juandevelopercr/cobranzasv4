<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Transaction;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class TransactionTotals extends Component
{
  public $transaction_id;
  public $totalHonorarios = 0;
  public $totalTimbres = 0;
  public $totalDiscount = 0;
  public $totalTax = 0;
  public $totalAditionalCharge = 0;

  public $totalServGravados = 0;
  public $totalServExentos = 0;
  public $totalServExonerado = 0;
  public $totalServNoSujeto = 0;

  public $totalMercGravadas = 0;
  public $totalMercExentas = 0;
  public $totalMercExonerada = 0;
  public $totalMercNoSujeta = 0;

  public $totalGravado = 0;
  public $totalExento = 0;
  public $totalVenta = 0;
  public $totalVentaNeta = 0;
  public $totalExonerado = 0;
  public $totalNoSujeto = 0;
  public $totalImpAsumEmisorFabrica = 0;
  public $totalImpuesto = 0;
  public $totalIVADevuelto = 0;
  public $totalOtrosCargos = 0;
  public $totalComprobante = 0;

  public $currencyCode = '';

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data)
  {
    $this->resetControls();
    $this->transaction_id = $data['transaction_id'];
    $this->refreshTotal($this->transaction_id);
  }

  public function mount($transaction_id)
  {
    $transaction = Transaction::find($transaction_id);
    if ($transaction) {

      $this->totalHonorarios = Helpers::formatDecimal($transaction->totalHonorarios ?? 0);
      $this->totalTimbres = Helpers::formatDecimal($transaction->totalTimbres ?? 0);
      $this->totalAditionalCharge = Helpers::formatDecimal($transaction->totalAditionalCharge ?? 0);

      $this->totalServGravados = Helpers::formatDecimal($transaction->totalServGravados ?? 0);
      $this->totalServExentos = Helpers::formatDecimal($transaction->totalServExentos ?? 0);
      $this->totalServExonerado = Helpers::formatDecimal($transaction->totalServExonerado ?? 0);
      $this->totalServNoSujeto = Helpers::formatDecimal($transaction->totalServNoSujeto ?? 0);

      $this->totalMercGravadas = Helpers::formatDecimal($transaction->totalMercGravadas ?? 0);
      $this->totalMercExentas = Helpers::formatDecimal($transaction->totalMercExentas ?? 0);
      $this->totalMercExonerada = Helpers::formatDecimal($transaction->totalMercExonerada ?? 0);
      $this->totalMercNoSujeta = Helpers::formatDecimal($transaction->totalMercNoSujeta ?? 0);

      $this->totalGravado = Helpers::formatDecimal($transaction->totalGravado ?? 0);
      $this->totalExento = Helpers::formatDecimal($transaction->totalExento ?? 0);
      $this->totalExonerado = Helpers::formatDecimal($transaction->totalExonerado ?? 0);
      $this->totalNoSujeto = Helpers::formatDecimal($transaction->totalNoSujeto ?? 0);

      $this->totalVenta = Helpers::formatDecimal($transaction->totalVenta ?? 0);
      $this->totalDiscount = Helpers::formatDecimal($transaction->totalDiscount ?? 0);
      $this->totalVentaNeta = Helpers::formatDecimal($transaction->totalVentaNeta ?? 0);
      $this->totalTax = Helpers::formatDecimal($transaction->totalTax ?? 0);
      $this->totalImpuesto = Helpers::formatDecimal($transaction->totalImpuesto ?? 0);
      $this->totalImpAsumEmisorFabrica = Helpers::formatDecimal($transaction->totalImpAsumEmisorFabrica ?? 0);
      $this->totalIVADevuelto = Helpers::formatDecimal($transaction->totalIVADevuelto ?? 0);
      $this->totalOtrosCargos = Helpers::formatDecimal($transaction->totalOtrosCargos ?? 0);
      $this->totalComprobante = Helpers::formatDecimal($transaction->totalComprobante ?? 0);

      $this->currencyCode = $transaction->currency->code;
    }
  }

  #[On('productUpdated')]
  #[On('chargeUpdated')]
  public function refreshTotal($transaction_id)
  {
    $this->resetControls();
  //  dd($this);
    $transaction = Transaction::where('id', $transaction_id)->first();
    if ($transaction) {
      $this->totalHonorarios = Helpers::formatDecimal($transaction->totalHonorarios ?? 0);
      $this->totalTimbres = Helpers::formatDecimal($transaction->totalTimbres ?? 0);
      $this->totalAditionalCharge = Helpers::formatDecimal($transaction->totalAditionalCharge ?? 0);

      $this->totalServGravados = Helpers::formatDecimal($transaction->totalServGravados ?? 0);
      $this->totalServExentos = Helpers::formatDecimal($transaction->totalServExentos ?? 0);
      $this->totalServExonerado = Helpers::formatDecimal($transaction->totalServExonerado ?? 0);
      $this->totalServNoSujeto = Helpers::formatDecimal($transaction->totalServNoSujeto ?? 0);

      $this->totalMercGravadas = Helpers::formatDecimal($transaction->totalMercGravadas ?? 0);
      $this->totalMercExentas = Helpers::formatDecimal($transaction->totalMercExentas ?? 0);
      $this->totalMercExonerada = Helpers::formatDecimal($transaction->totalMercExonerada ?? 0);
      $this->totalMercNoSujeta = Helpers::formatDecimal($transaction->totalMercNoSujeta ?? 0);

      $this->totalGravado = Helpers::formatDecimal($transaction->totalGravado ?? 0);
      $this->totalExento = Helpers::formatDecimal($transaction->totalExento ?? 0);
      $this->totalExonerado = Helpers::formatDecimal($transaction->totalExonerado ?? 0);
      $this->totalNoSujeto = Helpers::formatDecimal($transaction->totalNoSujeto ?? 0);

      $this->totalVenta = Helpers::formatDecimal($transaction->totalVenta ?? 0);
      $this->totalDiscount = Helpers::formatDecimal($transaction->totalDiscount ?? 0);
      $this->totalVentaNeta = Helpers::formatDecimal($transaction->totalVentaNeta ?? 0);
      $this->totalTax = Helpers::formatDecimal($transaction->totalTax ?? 0);
      $this->totalImpuesto = Helpers::formatDecimal($transaction->totalImpuesto ?? 0);
      $this->totalImpAsumEmisorFabrica = Helpers::formatDecimal($transaction->totalImpAsumEmisorFabrica ?? 0);
      $this->totalIVADevuelto = Helpers::formatDecimal($transaction->totalIVADevuelto ?? 0);
      $this->totalOtrosCargos = Helpers::formatDecimal($transaction->totalOtrosCargos ?? 0);
      $this->totalComprobante = Helpers::formatDecimal($transaction->totalComprobante ?? 0);

      $this->currencyCode = $transaction->currency->code;


      $this->dispatch('honorarios-changed', $this->totalHonorarios);
      /*
      if ($this->honorarios > 0)
        Log::debug('Honorarios actualizado', ['honorarios' => $this->honorarios]);
      */
      //$this->dispatch('honorarioUpdated', $this->honorarios);  // Emitir evento para otros componentes
    }
  }

  public function render()
  {
    return view('livewire.transactions.transaction-totals');
  }

  public function resetControls()
  {
    $this->transaction_id = null;

    $this->totalHonorarios = 0;
    $this->totalTimbres = 0;
    $this->totalDiscount = 0;
    $this->totalTax = 0;
    $this->totalAditionalCharge = 0;

    $this->totalServGravados = 0;
    $this->totalServExentos = 0;
    $this->totalServExonerado = 0;
    $this->totalServNoSujeto = 0;

    $this->totalMercGravadas = 0;
    $this->totalMercExentas = 0;
    $this->totalMercExonerada = 0;
    $this->totalMercNoSujeta = 0;

    $this->totalGravado = 0;
    $this->totalExento = 0;
    $this->totalVenta = 0;
    $this->totalVentaNeta = 0;
    $this->totalExonerado = 0;
    $this->totalNoSujeto = 0;
    $this->totalImpAsumEmisorFabrica = 0;
    $this->totalImpuesto = 0;
    $this->totalIVADevuelto = 0;
    $this->totalOtrosCargos = 0;
    $this->totalComprobante = 0;

    $this->currencyCode = '';
  }
}
