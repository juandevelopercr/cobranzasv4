<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public function build()
  {
    return $this->subject('Correo de prueba')
      ->view('emails.test');
  }
}
