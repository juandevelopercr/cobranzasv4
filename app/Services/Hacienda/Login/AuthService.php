<?php

namespace App\Services\Hacienda\Login;

class AuthService
{
  protected $token;

  public function __construct(?string $locationEnvironment = null)
  {
    $this->token = new Token($locationEnvironment);
  }

  /**
   * Obtiene el token de autenticación.
   *
   * @param string $issuerId
   * @param string $username
   * @param string $password
   *
   * @return string
   * @throws Exception
   */
  public function getToken($username, $password)
  {
    return $this->token->getToken($username, $password);
  }

  /**
   * Cierra la sesión.
   *
   * @param string $issuerId
   * @param string $refreshToken
   *
   * @return bool
   * @throws Exception
   */
  public function closeSession($issuerId, $refreshToken)
  {
    return $this->token->closeSession($issuerId, $refreshToken);
  }
}
