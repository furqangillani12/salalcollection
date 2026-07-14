<?php

// app/Exceptions/Handler.php
namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    //...

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedException) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }

        return parent::render($request, $exception);
    }
}
