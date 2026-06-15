<?php

namespace App\Services;

use App\Models\BusinessLocation;

class CertValidationService
{
  //Devuelve el número de serie del certificado en formato hexadecimal.
  //$serial = $service->getSerialNumber($location);

  //Retorna true si el certificado vence dentro de los próximos X días (por defecto 30).
  //$vencePronto = $service->expiresSoon($location); // dentro de 30 días
  //$venceEn15 = $service->expiresSoon($location, 15); // dentro de 15 días

  // Verifica si el certificado es válido y no está vencido
  //$esValido = $service->isCertValid($location);

  // Retorna la información completa del certificado
  //$info = $service->getCertInfo($location);

  public function isCertValid(BusinessLocation $location): bool
  {
    $cert = $this->getCertInfo($location);

    if (!$cert) {
      return false;
    }

    $now = time();
    return isset($cert['validTo_time_t']) && $now <= $cert['validTo_time_t'];
  }

  /*
  Esto para archivos .p12
  public function getCertInfo(BusinessLocation $location): ?array
  {
    $pfxRelativePath = $location->certificate_digital_file;
    $pin = trim($location->certificate_pin);

    $pfxPath = public_path("storage/assets/certificates/{$pfxRelativePath}");

    if (!file_exists($pfxPath)) {
      return null;
    }

    $pfxContent = file_get_contents($pfxPath);
    $certData = [];

    if (!openssl_pkcs12_read($pfxContent, $certData, $pin)) {

      return null;
    }
    return openssl_x509_parse($certData['cert']);
  }
  */

  public function getCertInfo(BusinessLocation $location): ?array
  {
    $relativePath = $location->certificate_digital_file;
    $certPath = public_path("storage/assets/certificates/{$relativePath}");

    if (empty($relativePath) || !file_exists($certPath)) {
      return null;
    }

    $ext = strtolower(pathinfo($certPath, PATHINFO_EXTENSION));

    if ($ext === 'p12' || $ext === 'pfx') {
      $pin = trim($location->certificate_pin ?? '');
      $certData = file_get_contents($certPath);

      if (openssl_pkcs12_read($certData, $key, $pin)) {
        return openssl_x509_parse($key['cert']);
      }

      // Fallback para P12 legacy de Hacienda (RC2/3DES) que fallan con OpenSSL 3.0
      $errors = [];
      while ($msg = openssl_error_string()) {
        $errors[] = $msg;
      }
      $errorStr = implode(' | ', $errors);

      if ((strpos($errorStr, 'unsupported') !== false || strpos($errorStr, 'digital envelope') !== false) && function_exists('shell_exec')) {
        $pinEscaped = escapeshellarg($pin);
        $pathEscaped = escapeshellarg($certPath);
        $cmd = "openssl pkcs12 -in $pathEscaped -passin pass:$pinEscaped -nodes -legacy 2>/dev/null | openssl pkcs12 -export -passout pass:$pinEscaped 2>/dev/null";
        $modernData = shell_exec($cmd);
        if ($modernData && openssl_pkcs12_read($modernData, $key, $pin)) {
          return openssl_x509_parse($key['cert']);
        }
      }

      return null;
    }

    // PEM
    $cert = openssl_x509_read(file_get_contents($certPath));
    return $cert ? openssl_x509_parse($cert) : null;
  }

  public function getSerialNumber(BusinessLocation $location): ?string
  {
    $cert = $this->getCertInfo($location);
    return $cert['serialNumberHex'] ?? null;
  }

  public function expiresSoon(BusinessLocation $location, int $days = 30): bool
  {
    $cert = $this->getCertInfo($location);

    if (!$cert || !isset($cert['validTo_time_t'])) {
      return false;
    }

    $now = time();
    $limit = $now + ($days * 86400);

    return $cert['validTo_time_t'] <= $limit;
  }
}
