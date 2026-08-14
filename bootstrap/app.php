<?php

use App\Exceptions\Handler;
use App\Http\Kernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
*/

$app->singleton(
    HttpKernelContract::class,
    Kernel::class
);

$app->singleton(
    ConsoleKernelContract::class,
    App\Console\Kernel::class
);

$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

return $app;
