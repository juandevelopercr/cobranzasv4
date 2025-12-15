<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use App\Livewire\Transactions\CuentaPorCobrarManager;

class CuentasPorCobrarReport extends BaseReport
{
  protected array $params;
  protected $externalQuery = null;

  public function __construct(array $params, $title = 'Cuentas por Cobrar', $externalQuery = null)
  {
    parent::__construct($params, $title);
    $this->params = $params;
    $this->externalQuery = $externalQuery;
  }

  public function map($row): array
  {
    $output = [];

    foreach ($this->columns() as $col) {
      $field = $col['field'] ?? null;

      if (!$field) {
        $output[] = null;
        continue;
      }

      $type = $col['type'] ?? 'string';

      // Special handling for payment_status to return readable text
      if ($field === 'payment_status') {
        switch ($row->payment_status) {
          case 'annulled':
            $output[] = __('ANULADA');
            break;
          case 'partial':
            $output[] = __('PARCIAL');
            break;
          case 'paid':
            $output[] = __('PAGADO');
            break;
          case 'due':
          default:
            $output[] = __('PENDIENTE');
            break;
        }
        continue;
      }

      $value = $row->{$field} ?? null;

      // Type-specific formatting
      if ($type === 'date') {
        if ($value instanceof \DateTimeInterface) {
          $output[] = $value->format('d-m-Y');
          continue;
        }
        if (is_string($value) && !empty($value)) {
          try {
            $dt = Carbon::createFromFormat('Y-m-d', $value);
          } catch (\Exception $e) {
            try {
              $dt = Carbon::createFromFormat('d-m-Y', $value);
            } catch (\Exception $e2) {
              try {
                $dt = Carbon::parse($value);
              } catch (\Exception $e3) {
                $dt = null;
              }
            }
          }
          $output[] = $dt ? $dt->format('d-m-Y') : null;
          continue;
        }
        $output[] = null;
        continue;
      }

      if (in_array($type, ['decimal', 'currency'])) {
        if (is_null($value) || $value === '') {
          $output[] = null;
        } else {
          $normalized = preg_replace('/[^0-9,\.\-]/', '', (string) $value);
          if (strpos($normalized, ',') !== false && strpos($normalized, '.') !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
          } else {
            $normalized = str_replace(',', '.', $normalized);
          }
          $output[] = is_numeric($normalized) ? (float) $normalized : null;
        }
        continue;
      }

      if ($type === 'integer') {
        $output[] = is_numeric($value) ? (int)$value : null;
        continue;
      }

      // Default: string — strip HTML
      if (is_string($value)) $value = trim(strip_tags($value));
      $output[] = $value;
    }

    return $output;
  }

  protected function columns(): array
  {
    try {
      $manager = new CuentaPorCobrarManager();
      $default = $manager->getDefaultColumns();
    } catch (\Throwable $e) {
      $default = [
        ['label' => 'ID', 'field' => 'id', 'columnType' => 'integer', 'columnAlign' => 'left', 'width' => 10],
        ['label' => 'Consecutivo', 'field' => 'consecutivo', 'columnType' => 'string', 'columnAlign' => 'center', 'width' => 30],
      ];
    }

    $cols = [];
    foreach ($default as $c) {
      if (isset($c['visible']) && $c['visible'] === false) continue;

      $colType = $c['columnType'] ?? ($c['type'] ?? 'string');
      switch ($colType) {
        case 'date':
          $type = 'date';
          break;
        case 'decimal':
        case 'currency':
          $type = 'decimal';
          break;
        case 'integer':
          $type = 'integer';
          break;
        default:
          $type = 'string';
      }

      $cols[] = [
        'label' => $c['label'] ?? ucfirst($c['field'] ?? ''),
        'field' => $c['field'] ?? '',
        'type' => $type,
        'align' => $c['columnAlign'] ?? ($c['columnAlign'] ?? 'left'),
        'width' => 30,
      ];
    }

    return $cols;
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    if ($this->externalQuery !== null) {
      return $this->externalQuery;
    }

    $search = $this->params['search'] ?? '';
    $filters = $this->params['filters'] ?? [];
    $sortBy = $this->params['sortBy'] ?? 'transactions.transaction_date';
    $sortDir = $this->params['sortDir'] ?? 'DESC';
    $perPage = $this->params['perPage'] ?? 10;
    $page = $this->params['page'] ?? 1;
    $selectedIds = $this->params['selectedIds'] ?? [];

    $query = Transaction::search($search, $filters)
      ->orderBy($sortBy, $sortDir);

    if (!empty($selectedIds)) {
      $query->whereIn('transactions.id', $selectedIds);
      return $query;
    }

    $offset = max(((int)$page - 1) * (int)$perPage, 0);
    $query = $query->skip($offset)->take((int)$perPage);

    return $query;
  }
}
