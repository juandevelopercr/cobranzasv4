<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Comprobante;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class ProcessComprobanteEmails extends Command
{
  /*
  php artisan comprobantes:process-emails
  */

  protected $signature = 'comprobantes:process-emails';
  protected $description = 'Procesa emails con comprobantes electrónicos';

  public function handle()
  {
    Log::channel('scheduler')->info('Iniciando procesamiento de emails con comprobantes');

    $business = Business::first();
    if (!$business) {
      Log::channel('scheduler')->error('No se encontró configuración de negocio');
      $this->error('No business configuration found');
      return;
    }

    // Configuración óptima para IMAP SSL
    $host = $business->host_imap;
    $port = $business->puerto_imap;
    $encryption = $business->imap_encryptation;
    $validateCert = false;

    Log::channel('scheduler')->debug('Información de conexion a imap', [
      'host' => $business->host_imap,
      'port' => $business->puerto_imap,
      'user' => $business->user_imap,
      'encryption' => $business->imap_encryptation
    ]);

    // Mostrar en consola la información (sin exponer la contraseña)
    $this->info("Conectando a IMAP:");
    $this->line("  Host: {$host}");
    $this->line("  Port: {$port}");
    $this->line("  User: {$business->user_imap}");
    $this->line("  Encryption: {$encryption}");
    // Si quieres mostrar la pass, mejor enmascarada
    $this->line("  Pass: " . str_repeat('*', strlen($business->pass_imap)));

    //$this->info("Conectando a: {$host}:{$port} (SSL)");

    try {
      // Configuración para ClientManager (v6.x)
      $clientManager = new ClientManager([
        'accounts' => [
          'default' => [
            'host'          => $host,
            'port'          => $port,
            'encryption'    => $encryption,
            'validate_cert' => $validateCert,
            'username'      => $business->user_imap,
            'password'      => $business->pass_imap,
            'protocol'      => 'imap',
            'timeout'       => 30
          ]
        ]
      ]);

      $this->info("Creando cliente IMAP...");
      $client = $clientManager->account('default');

      $this->info("Estableciendo conexión...");
      $client->connect();

      $this->info("Verificando carpetas requeridas...");
      $this->ensureFoldersExist($client);

      $this->info("✓ Conexión exitosa. Obteniendo bandeja de entrada...");
      $inbox = $client->getFolder('INBOX');
      $messages = $inbox->messages()->all()->get();

      $this->info("Procesando " . count($messages) . " mensajes...");
      foreach ($messages as $message) {
        $this->processMessage($message, $business);
      }

      $client->disconnect();
      $this->info("✔ Proceso completado exitosamente");
    } catch (ConnectionFailedException $e) {
      $this->handleConnectionError($e, $host);
    } catch (\Throwable $e) {
      $this->handleGenericError($e);
    }
  }

  /**
   * Asegura que las carpetas usadas por el procesamiento existan en el buzón IMAP.
   *
   * IMPORTANTE: Client::getFolder() de webklex/php-imap devuelve null cuando la
   * carpeta no existe, NO lanza excepción — por eso el chequeo se hace con
   * is_null() y no con try/catch alrededor de getFolder().
   */
  private function ensureFoldersExist($client): void
  {
    $folders = ['PROCESADOS', 'ERRORES', 'RECHAZADOS', 'DUPLICADOS'];

    foreach ($folders as $name) {
      try {
        $folder = $client->getFolder($name);
        if (is_null($folder)) {
          $this->info("Carpeta '{$name}' no existe. Creando...");
          Log::channel('scheduler')->info("Carpeta '{$name}' no existe en el buzón. Creando...");

          // $expunge=false: createFolder() por defecto manda un EXPUNGE justo
          // después de crear la carpeta, y EXPUNGE requiere una carpeta ya
          // seleccionada (RFC 3501). En este punto todavía no se ha seleccionado
          // ninguna (INBOX se selecciona después), así que el servidor respondía
          // "BAD No mailbox selected" y la carpeta nunca terminaba de crearse.
          $newFolder = $client->createFolder($name, false);

          // Crear una carpeta IMAP no la suscribe automáticamente en muchos
          // servidores — sin esto queda invisible en el webmail (Roundcube y
          // similares solo muestran carpetas suscritas) aunque el sistema sí
          // la use para mover los correos.
          try {
            $newFolder?->subscribe();
          } catch (\Exception $e) {
            Log::channel('scheduler')->warning("No se pudo suscribir la carpeta '{$name}': " . $e->getMessage());
          }

          $this->info("✓ Carpeta '{$name}' creada correctamente.");
        }
      } catch (\Exception $e) {
        $this->error("No se pudo verificar/crear la carpeta '{$name}': " . $e->getMessage());
        Log::channel('scheduler')->error("No se pudo verificar/crear la carpeta '{$name}': " . $e->getMessage());
      }
    }
  }

  /**
   * Mueve el mensaje a la carpeta indicada; si el MOVE del servidor IMAP falla
   * (algunos proveedores devuelven errores intermitentes), al menos lo marca
   * como leído para que quede visible que algo pasó con él, en vez de fallar
   * en silencio y dejarlo indefinidamente sin ningún rastro de haber sido
   * tocado.
   */
  private function moveOrMarkSeen($message, string $folder): void
  {
    try {
      $message->move($folder);
    } catch (\Throwable $e) {
      $this->error("No se pudo mover el mensaje a '{$folder}': " . $e->getMessage());
      Log::channel('scheduler')->warning("No se pudo mover el correo a '{$folder}' ({$e->getMessage()}). Se marca como leído.");
      try {
        $message->setFlag('Seen');
      } catch (\Throwable $inner) {
        Log::channel('scheduler')->error("Tampoco se pudo marcar el correo como leído: " . $inner->getMessage());
      }
    }
  }

  protected function handleConnectionError($e, $host)
  {
    $errorMsg = "Error de conexión IMAP: " . $e->getMessage();
    $this->error($errorMsg);
    Log::channel('scheduler')->error($errorMsg);

    $this->error("\nPosibles soluciones:");
    $this->line("1. Verifica las credenciales con un cliente como Thunderbird");
    $this->line("2. Prueba con:");
    $this->line("   - 'validate_cert' => false");
    $this->line("   - Puerto 143 con 'encryption' => 'tls'");
    $this->line("3. Prueba la conexión manual:");
    $this->line("   openssl s_client -connect {$host}:993 -crlf");
  }

  protected function handleGenericError($e)
  {
    $errorMsg = "Error: " . $e->getMessage();
    $this->error($errorMsg);
    Log::channel('scheduler')->error($errorMsg, [
      'exception' => $e,
      'trace' => $e->getTraceAsString()
    ]);
  }

  private function processMessage($message, Business $business)
  {
    try {
      $this->info("Procesando mensaje ID: " . $message->getUid());

      $attachments = $message->getAttachments();
      $this->info("Adjuntos encontrados: " . count($attachments));

      if (count($attachments) === 0) {
        $this->warn("⚠ Mensaje sin adjuntos - Moviendo a RECHAZADOS");
        $this->moveOrMarkSeen($message, 'RECHAZADOS');
        return;
      }

      $comprobanteData = null;
      $xmlComprobante = null;
      $xmlRespuesta = null;
      $pdf = null;

      foreach ($attachments as $attachment) {
        try {
          $extension = strtolower(pathinfo($attachment->name, PATHINFO_EXTENSION));
          $this->info("Procesando adjunto: " . $attachment->name);

          if ($extension === 'xml') {
            $content = $attachment->content;
            if (empty($content)) {
              $this->warn("XML vacío en adjunto: " . $attachment->name);
              continue;
            }

            $xml = $this->parseXml($content);
            if (!$xml) {
              $this->warn("XML inválido en adjunto: " . $attachment->name);
              continue;
            }

            if ($this->isComprobanteXml($xml)) {
              $comprobanteData = $this->extractComprobanteData($xml, $business);
              $xmlComprobante = $content;
              $this->info("✓ XML de comprobante válido encontrado");
            } elseif ($this->isRespuestaXml($xml)) {
              $xmlRespuesta = $content;
              $this->info("✓ XML de respuesta encontrado");
            }
          } elseif ($extension === 'pdf') {
            $pdf = $attachment->content;
            $this->info("✓ PDF adjunto encontrado");
          }
        } catch (\Exception $e) {
          $this->error("Error procesando adjunto: " . $e->getMessage());
          Log::channel('scheduler')->error("Error en adjunto " . $attachment->name . ": " . $e->getMessage());
        }
      }

      if ($comprobanteData) {
        // Validar que la fecha de emisión de la factura no haya expirado
        if (!empty($comprobanteData['fecha_emision'])) {
          try {
            $fechaEmision = \Carbon\Carbon::parse($comprobanteData['fecha_emision']);

            $fechaLimite = $fechaEmision->copy()->startOfMonth()->addMonth();
            $businessDays = 0;

            // Definir feriados de Costa Rica
            $getHolidays = function ($year) {
                $easterDays = easter_days($year);
                $easterDate = \Carbon\Carbon::createFromDate($year, 3, 21)->addDays($easterDays);
                $juevesSanto = $easterDate->copy()->subDays(3)->format('m-d');
                $viernesSanto = $easterDate->copy()->subDays(2)->format('m-d');

                return [
                    '01-01', // Año Nuevo
                    '04-11', // Día de Juan Santamaría
                    '05-01', // Día del Trabajador
                    '07-25', // Anexión del Partido de Nicoya
                    '08-02', // Virgen de los Ángeles
                    '08-15', // Día de la Madre
                    '09-15', // Día de la Independencia
                    '12-01', // Día de la Abolición del Ejército
                    '12-25', // Navidad
                    $juevesSanto,
                    $viernesSanto,
                ];
            };

            while ($businessDays < 8) {
              $holidays = $getHolidays($fechaLimite->year);
              $isHoliday = in_array($fechaLimite->format('m-d'), $holidays);

              if ($fechaLimite->isWeekday() && !$isHoliday) {
                $businessDays++;
              }
              if ($businessDays < 8) {
                $fechaLimite->addDay();
              }
            }
            $fechaLimite->endOfDay();

            $hoy = \Carbon\Carbon::now();

            if ($hoy->gt($fechaLimite)) {
              $this->info("La fecha de emisión de la factura ha expirado. (Válido hasta 8 días hábiles del mes siguiente a la emisión). No se procesará este comprobante.");
              $this->moveOrMarkSeen($message, 'RECHAZADOS');
              return;
            }
          } catch (\Exception $e) {
            $this->error("No se pudo validar la fecha de emisión del comprobante. No se procesará este comprobante.");
            $this->moveOrMarkSeen($message, 'RECHAZADOS');
            return;
          }
        }

        // Verificar duplicado con más criterios (opcional)
        $existing = Comprobante::where('key', $comprobanteData['key'])
          ->where('status', '!=', 'ERROR')
          ->first();

        if ($existing) {
          $this->info("✔ Comprobante ya existe [ID: {$existing->id}]: {$comprobanteData['key']} - Moviendo a DUPLICADOS");

          // Opcional: Actualizar fecha de último visto
          $existing->touch();

          // Mover el mensaje a una carpeta dedicada para duplicados: nunca se
          // deja sin procesar ni se mezcla con los comprobantes recién creados
          // en PROCESADOS, para poder auditarlos por separado.
          $this->moveOrMarkSeen($message, 'DUPLICADOS');
          Log::channel('scheduler')->info("Comprobante duplicado manejado", [
            'comprobante_id' => $existing->id,
            'key' => $comprobanteData['key']
          ]);
          return;
        }

        try {
          $comprobante = $this->createComprobante($comprobanteData, $xmlComprobante, $xmlRespuesta, $pdf);

          // createComprobante() captura sus propias excepciones y devuelve null
          // en vez de relanzarlas (ver su catch interno) — sin este chequeo el
          // mensaje se marcaba como PROCESADOS y luego el acceso a
          // $comprobante->key producía un TypeError (\Error, no \Exception) que
          // el catch de abajo NO captura, abortando todo el comando a mitad del
          // lote y dejando el resto de correos del INBOX sin siquiera intentarse.
          if (!$comprobante) {
            throw new \Exception("createComprobante() no devolvió un comprobante válido");
          }

          $this->moveOrMarkSeen($message, 'PROCESADOS');
          $this->info("✔ Comprobante creado: " . $comprobante->key);
        } catch (\Throwable $e) {
          $this->error("Error creando comprobante: " . $e->getMessage());
          Log::channel('scheduler')->error("Error creando comprobante: " . $e->getMessage(), [
            'key' => $comprobanteData['key'] ?? null,
            'trace' => $e->getTraceAsString()
          ]);
          $this->moveOrMarkSeen($message, 'ERRORES');
        }
      } else {
        $this->warn("⚠ No se encontró XML válido - Moviendo a RECHAZADOS");
        $this->moveOrMarkSeen($message, 'RECHAZADOS');
      }
    } catch (\Throwable $e) {
      // \Throwable (no solo \Exception) a propósito: un \Error (p.ej. TypeError)
      // en un solo mensaje no debe abortar el foreach de handle() y dejar sin
      // siquiera intentar el resto del lote de correos pendientes.
      $this->error("Error procesando mensaje: " . $e->getMessage());
      Log::channel('scheduler')->error("Error procesando mensaje: " . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
      ]);

      // No dejar el mensaje atascado indefinidamente en INBOX reintentándose
      // en cada corrida sin nunca resolverse: se manda a ERRORES para revisión
      // manual, igual que cuando falla la creación del comprobante.
      $this->moveOrMarkSeen($message, 'ERRORES');
    }
  }

  private function parseXml(string $content): ?SimpleXMLElement
  {
    try {
      return new SimpleXMLElement($content);
    } catch (\Exception $e) {
      Log::channel('scheduler')->warning('XML inválido: ' . $e->getMessage());
      return null;
    }
  }

  private function isComprobanteXml(SimpleXMLElement $xml): bool
  {
    $rootNode = $xml->getName();
    return in_array($rootNode, [
      'FacturaElectronica',
      'NotaDebitoElectronica',
      'NotaCreditoElectronica',
      'TiqueteElectronico',
      'FacturaElectronicaCompra',
      'FacturaElectronicaExportacion',
      'ReciboElectronicoPago'
    ]);
  }

  private function isRespuestaXml(SimpleXMLElement $xml): bool
  {
    return $xml->getName() === 'MensajeHacienda';
  }

  private function extractComprobanteData(SimpleXMLElement $xml, Business $business): ?array
  {
    try {
      $tipoDocumento = $this->getTipoDocumento($xml);
      $emisorId = (string)$xml->Emisor->Identificacion->Numero;
      $receptorId = (string)$xml->Receptor->Identificacion->Numero;

      // Validar que pertenezca a la empresa
      $location = BusinessLocation::where('identification', $receptorId)->first();
      if (!$location) {
        $location = BusinessLocation::where('identification', $emisorId)->first();
      }

      if (!$location) {
        Log::channel('scheduler')->warning('Ubicación no encontrada para: ' . $receptorId . ' o ' . $emisorId);
        return null;
      }

      return [
        'location_id' => $location->id,
        'key' => (string)$xml->Clave,
        'fecha_emision' => (string)$xml->FechaEmision,
        'codigo_actividad' => (string)($xml->CodigoActividadEmisor ?? ''),
        'codigo_actividad_receptor' => (string)($xml->CodigoActividadReceptor ?? ''),
        'situacion_comprobante' => (string)($xml->SituacionComprobante ?? '1'),

        'emisor_nombre' => (string)$xml->Emisor->Nombre,
        'emisor_tipo_identificacion' => (string)$xml->Emisor->Identificacion->Tipo,
        'emisor_numero_identificacion' => $emisorId,

        'receptor_nombre' => (string)$xml->Receptor->Nombre ?? '',
        'receptor_tipo_identificacion' => (string)$xml->Receptor->Identificacion->Tipo ?? '',
        'receptor_numero_identificacion' => $receptorId,

        'tipo_cambio' => (float)($xml->ResumenFactura->CodigoTipoMoneda->TipoCambio ?? 1),
        'total_comprobante' => (float)$xml->ResumenFactura->TotalComprobante,
        'total_impuestos' => (float)($xml->ResumenFactura->TotalImpuesto ?? 0),
        'total_gravado' => (float)($xml->ResumenFactura->TotalGravado ?? 0),
        'total_exento' => (float)($xml->ResumenFactura->TotalExento ?? 0),
        'total_descuentos' => (float)($xml->ResumenFactura->TotalDescuentos ?? 0),
        'moneda' => (string)($xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda ?? 'CRC'),

        'condicion_venta' => (string)($xml->CondicionVenta ?? '01'),
        'plazo_credito' => (int)($xml->PlazoCredito ?? 0),
        'medio_pago' => (string)($xml->MedioPago ?? '01'),
        'clave_referencia' => (string)($xml->NumeroDocumento ?? ''), // Para NC/ND
        'status' => 'PENDIENTE',

        'tipo_documento' => $tipoDocumento,
        'detalle' => 'Comprobante Aceptado',
        'mensajeConfirmacion' => 'ACEPTADO',
      ];
    } catch (\Exception $e) {
      Log::channel('scheduler')->error('Error extrayendo datos comprobante: ' . $e->getMessage());
      return null;
    }
  }

  private function getTipoDocumento(SimpleXMLElement $xml): string
  {
    $rootNode = $xml->getName();
    $map = [
      'FacturaElectronica' => '01',
      'NotaDebitoElectronica' => '02',
      'NotaCreditoElectronica' => '03',
      'TiqueteElectronico' => '04',
      'mensajeReceptor' => '05',
      'mensajeReceptor' => '06',
      'mensajeReceptor' => '07',
      'FacturaElectronicaCompra' => '08',
      'FacturaElectronicaExportacion' => '09',
      'ReciboElectronicoPago' => '10',
    ];

    return $map[$rootNode] ?? '01';
  }

  private function createComprobante(array $data, string $xmlComprobante, ?string $xmlRespuesta, ?string $pdf): ?Comprobante
  {
    // Variables para almacenar las rutas de los archivos creados
    $comprobantePath = null;
    $respuestaPath = null;
    $pdfPath = null;

    try {
      $locationId = $data['location_id'];
      $fechaEmision = Carbon::parse($data['fecha_emision']);
      $year = $fechaEmision->format('Y');
      $month = $fechaEmision->format('m');

      // Crear estructura de carpetas
      $basePath = "comprobantes/{$locationId}/{$year}/{$month}";
      Storage::disk('public')->makeDirectory($basePath);

      // Guardar archivos
      $comprobanteFilename = $data['key'] . '.xml';
      $comprobantePath = "{$basePath}/{$comprobanteFilename}";
      $bytesWritten = Storage::disk('public')->put($comprobantePath, $xmlComprobante);

      if ($bytesWritten === false) {
        throw new \Exception("Error al guardar el XML del comprobante");
      }

      if ($xmlRespuesta) {
        $respuestaFilename = $data['key'] . '_respuesta.xml';
        $respuestaPath = "{$basePath}/{$respuestaFilename}";
        if (Storage::disk('public')->put($respuestaPath, $xmlRespuesta) === false) {
          throw new \Exception("Error al guardar el XML de respuesta");
        }
      }

      if ($pdf) {
        $pdfFilename = $data['key'] . '.pdf';
        $pdfPath = "{$basePath}/{$pdfFilename}";
        if (Storage::disk('public')->put($pdfPath, $pdf) === false) {
          throw new \Exception("Error al guardar el PDF");
        }
      }

      // Crear registro en BD con transacción
      $comprobante = DB::transaction(function () use ($data, $comprobantePath, $respuestaPath, $pdfPath) {
        $comprobante = Comprobante::create([
          ...$data,
          'xml_path' => $comprobantePath,
          'xml_respuesta_path' => $respuestaPath,
          'pdf_path' => $pdfPath
        ]);

        return $comprobante;
      });

      // ============================================================
      // PUNTO 2: Ejecutar procesos adicionales después de crear el comprobante
      // ============================================================
      // Aquí puedes llamar a otros procesos que necesiten ejecutarse después de crear el comprobante
      $this->sendDocumentToHacienda($comprobante);

      return $comprobante;
    } catch (\Exception $e) {
      Log::channel('scheduler')->error("Error al crear comprobante: " . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'data' => [
          'key' => $data['key'] ?? null,
          'location_id' => $data['location_id'] ?? null
        ]
      ]);

      $this->error("Error al crear comprobante: " . $e->getMessage());

      // ============================================================
      // PUNTO 1: Limpiar archivos creados si hubo error
      // ============================================================
      // Eliminar archivos guardados si falla la transacción
      //$this->limpiarArchivosCreados($comprobantePath, $respuestaPath, $pdfPath);

      return null;
    }
  }

  private function sendDocumentToHacienda(Comprobante $comprobante)
  {
    Log::channel('scheduler')->info('Iniciando el envio del comprobante a hacienda en comando');

    // Obtener la secuencia que le corresponde según tipo de comprobante
    $secuencia = DocumentSequenceService::generateConsecutive(
      'MR',
      $comprobante->location_id
    );

    $consecutivo = $comprobante->getConsecutivo($secuencia);
    $comprobante->consecutivo = $consecutivo;
    $comprobante->save();

    // Obtener el xml firmado y en base64
    $encode = true;
    $xml = Helpers::generateMensajeElectronicoXML($comprobante, $encode, 'content');

    //Loguearme en hacienda para obtener el token
    $username = $comprobante->location->api_user_hacienda;
    $password = $comprobante->location->api_password;
    try {
      $authService = new AuthService($comprobante->location->environment);
      $token = $authService->getToken($username, $password);
    } catch (\Exception $e) {
      Log::channel('scheduler')->error('Ha ocurrido un error al intentar identificarse en la api de hacienda en comando: ' . $e->getMessage());
      throw new \Exception("Ha ocurrido un error al intentar identificarse en la api de hacienda en comando: " . $e->getMessage());
    }

    $tipoDocumento = $comprobante->getComprobanteCode();

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $comprobante, $comprobante->location, $tipoDocumento);
    if ($result['error'] == 0) {
      $comprobante->status = Comprobante::RECIBIDA;
      $comprobante->created_at = \Carbon\Carbon::now();
    } else {
      Log::channel('scheduler')->error('Ha ocurrido un error al enviar el comprobante a hacienda en comando: ' . $result['mensaje']);
      throw new \Exception('Ha ocurrido un error al enviar el comprobante a hacienda en comando: ' . $result['mensaje']);
    }

    // Guardar la transacción
    if (!$comprobante->save()) {
      Log::channel('scheduler')->error('Ha ocurrido un error al intentar guardar el comprobante en comando');
      throw new \Exception('Ha ocurrido un error al intentar guardar el comprobante en comando');
    }
  }

  // ============================================================
  // FUNCIÓN PARA LIMPIAR ARCHIVOS CREADOS (PUNTO 1)
  // ============================================================
  private function limpiarArchivosCreados(?string $comprobantePath, ?string $respuestaPath, ?string $pdfPath)
  {
    try {
      $disk = Storage::disk('public');

      if ($comprobantePath && $disk->exists($comprobantePath)) {
        $disk->delete($comprobantePath);
      }

      if ($respuestaPath && $disk->exists($respuestaPath)) {
        $disk->delete($respuestaPath);
      }

      if ($pdfPath && $disk->exists($pdfPath)) {
        $disk->delete($pdfPath);
      }

      $this->info("Archivos temporales eliminados después de error");
    } catch (\Exception $e) {
      Log::channel('scheduler')->error("Error al limpiar archivos: " . $e->getMessage(), [
        'paths' => compact('comprobantePath', 'respuestaPath', 'pdfPath')
      ]);
      $this->error("Error al limpiar archivos: " . $e->getMessage());
    }
  }
}
