<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentSequenceService
{
  /**
   * Genera el consecutivo para un tipo de documento.
   *
   * @param string $documentType
   * @param int|null $emitterId
   * @return string
   */
  /*
  public static function generateConsecutive($documentType, $emitterId = null, $userId = null, $initials = null): string
  {
    return DB::transaction(function () use ($documentType, $emitterId) {
      $userId = Auth::id();
      $initials = Auth::user()->initials;

      // Determinar si se filtra por user_id o emitter_id
      if ($documentType === Transaction::PROFORMA || $documentType === 'PRC' || $documentType === 'CASO') {
        // Proforma: Filtrar por usuario
        $sequence = DB::table('document_sequences')
          ->where('user_id', $userId)
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'user_id' => $userId,
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          $number = 1;
          return str_pad($number, 10, '0', STR_PAD_LEFT) . $initials;
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'updated_at' => now()]);

        return str_pad($newSequence, 10, '0', STR_PAD_LEFT) . $initials;
      } else {
        // Otros Documentos: Filtrar por emisor
        if (!$emitterId) {
          throw new \InvalidArgumentException('El emisor es requerido para este documento.');
        }

        $sequence = DB::table('document_sequences')
          ->where('emitter_id', $emitterId)
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'user_id' => $userId,
            'emitter_id' => $emitterId,
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          return str_pad(1, 10, '0', STR_PAD_LEFT);
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'user_id' => $userId, 'updated_at' => now()]);

        return str_pad($newSequence, 10, '0', STR_PAD_LEFT);
      }
    });
  }
  */


  public static function generateConsecutive($documentType, $emitterId = null, $userId = null, $initials = null): string
  {
    return DB::transaction(function () use ($documentType, $emitterId, $userId, $initials) {
      // Obtener usuario del contexto o usar admin por defecto
      if ($userId === null || $initials === null) {
        if (Auth::check()) {
          $userId = Auth::id();
          $initials = Auth::user()->initials;
        } else {
          // Usuario por defecto para contextos sin autenticación (cron, jobs, etc.)
          $defaultUser = User::find(1); // ID 1 = admin

          if (!$defaultUser) {
            throw new \Exception("Usuario por defecto no encontrado");
          }

          $userId = $defaultUser->id;
          $initials = $defaultUser->initials;
        }
      }

      // Determinar si se filtra por user_id o emitter_id
      if (in_array($documentType, [Transaction::PROFORMA, Transaction::NOTACREDITO, Transaction::NOTADEBITO, Transaction::COTIZACION, Transaction::PROFORMACOMPRA, Transaction::CASO])) {
        // Proforma: Filtrar por usuario
        $sequence = DB::table('document_sequences')
          ->where('user_id', $userId)
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'user_id' => $userId,
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          $number = 1;
          return str_pad($number, 10, '0', STR_PAD_LEFT) . $initials;
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'updated_at' => now()]);

        if (in_array($documentType, [Transaction::NOTACREDITO, Transaction::NOTADEBITO, Transaction::COTIZACION, Transaction::PROFORMACOMPRA, Transaction::CASO]))
          $initials = '';

        return str_pad($newSequence, 10, '0', STR_PAD_LEFT) . $initials;
      } else {
        // Otros Documentos: Filtrar por emisor
        if (!$emitterId) {
          throw new \InvalidArgumentException('El emisor es requerido para este documento.');
        }

        $sequence = DB::table('document_sequences')
          ->where('emitter_id', $emitterId)
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'user_id' => $userId,
            'emitter_id' => $emitterId,
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          return str_pad(1, 10, '0', STR_PAD_LEFT);
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'user_id' => $userId, 'updated_at' => now()]);

        return str_pad($newSequence, 10, '0', STR_PAD_LEFT);
      }
    });
  }

  public static function generateConsecutiveGasto($documentType): string
  {
    return DB::transaction(function () use ($documentType) {
      // Determinar si se filtra por user_id o emitter_id
      if ($documentType === Transaction::PROFORMA) {
        // Proforma: Filtrar por documento de gasto

        $documentType = Transaction::PROFORMAGASTO;

        $sequence = DB::table('document_sequences')
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          $number = 1;
          return $number;
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'updated_at' => now()]);

        return $newSequence;
      }
    });
  }

  public static function generateConsecutiveCaso($documentType): string
  {
    return DB::transaction(function () use ($documentType) {
      // Determinar si se filtra por user_id o emitter_id
      if ($documentType === Transaction::CASO) {
        // Proforma: Filtrar por documento de gasto

        $documentType = Transaction::PROFORMAGASTO;

        $sequence = DB::table('document_sequences')
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          $number = 1;
          return $number;
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'updated_at' => now()]);

        return $newSequence;
      }
    });
  }

  public static function generateConsecutiveNotaDigital($documentType): string
  {
    return DB::transaction(function () use ($documentType) {
      // Determinar si se filtra por user_id o emitter_id
      if ($documentType === Transaction::NOTACREDITO || $documentType === Transaction::NOTADEBITO) {
        // Proforma: Filtrar por documento de gasto

        $sequence = DB::table('document_sequences')
          ->where('document_type', $documentType)
          ->lockForUpdate()
          ->first();

        if (!$sequence) {
          DB::table('document_sequences')->insert([
            'document_type' => $documentType,
            'current_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now()
          ]);
          $number = 1;
          return $number;
        }

        $newSequence = $sequence->current_sequence + 1;
        DB::table('document_sequences')
          ->where('id', $sequence->id)
          ->update(['current_sequence' => $newSequence, 'updated_at' => now()]);

        return $newSequence;
      }
    });
  }
}
