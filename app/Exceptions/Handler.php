<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Disable collision error handler to avoid TypeError during artisan commands
        if (class_exists(\NunoMaduro\Collision\Provider::class)) {
            $this->app->singleton(
                \Illuminate\Contracts\Debug\ExceptionHandler::class,
                \Illuminate\Foundation\Exceptions\Handler::class
            );
        }

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
