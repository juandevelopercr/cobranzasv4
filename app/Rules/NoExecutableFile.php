<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoExecutableFile implements ValidationRule
{
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
        'sh', 'bash', 'zsh', 'ksh',
        'py', 'pyc', 'pyo',
        'pl', 'pm',
        'rb',
        'exe', 'bat', 'cmd', 'com', 'scr',
        'cgi',
        'asp', 'aspx', 'jsp', 'jspx',
        'htaccess', 'htpasswd',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value || !method_exists($value, 'getClientOriginalName')) {
            return;
        }

        $originalName = $value->getClientOriginalName();

        // Block double extensions (e.g. shell.php.jpg)
        $parts = explode('.', strtolower($originalName));
        foreach ($parts as $part) {
            if (in_array($part, self::BLOCKED_EXTENSIONS, true)) {
                $fail('El archivo contiene una extensión no permitida: .' . $part);
                return;
            }
        }

        // Block dangerous MIME types
        $blockedMimes = [
            'application/x-php',
            'text/x-php',
            'application/x-executable',
            'application/x-shellscript',
            'text/x-shellscript',
            'application/x-sh',
        ];

        if (in_array($value->getMimeType(), $blockedMimes, true)) {
            $fail('El tipo de archivo no está permitido por razones de seguridad.');
        }
    }
}
