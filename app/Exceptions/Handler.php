<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
        });
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        if ($request->is('api/*')) {
            return true;
        }

        return parent::shouldReturnJson($request, $e);
    }
}
