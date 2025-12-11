<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        Laravel\Fortify\FortifyServiceProvider::class,
        App\Providers\FortifyServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         // 1. Global middleware (append ou prepend)
        $middleware->append(
            
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            //\App\Models\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        );

        // 2. Middleware Groups
        $middleware->appendToGroup('web', [
            //\App\Models\Http\Middleware\EncryptCookies::class,
            //\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            //\Illuminate\View\Middleware\ShareErrorsFromSession::class,
            //\App\Models\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->appendToGroup('api', [
            //'throttle:api',
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            //'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            //'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

        // 3. Aliases (Route Middleware)
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'login'=>  \App\Http\Middleware\ChecarLogin::class,
            'liberar.recurso'=>  \App\Http\Middleware\LiberarRecurso::class,
            'login.perfil'=> \App\Http\Middleware\LoginPerfil::class
        ]);

        // 4. Se necessário, configure prioridade
        $middleware->priority([
            
            //\Illuminate\Cookie\Middleware\EncryptCookies::class,
            //\Illuminate\Session\Middleware\StartSession::class,
            //\Illuminate\Routing\Middleware\ThrottleRequests::class,
            //\Illuminate\Routing\Middleware\SubstituteBindings::class,
            //\Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        //disparador de fila de trabalho
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

        /*$schedule->call( function(){
                dispatch(new ControleFaltas);
            })->daily()->at('21:05');


        /*$schedule->call( function(){
                dispatch(new ControleBoletos);
            })->daily()->at('18:07');*/

         //coloca atestados como vencidos 
         //$schedule->command('app:verificar-atestados')->dailyAt('06:00');

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([ // <-- ADICIONE OU EDITE ESTE MÉTODO
        App\Providers\FortifyServiceProvider::class,
        // Outros provedores que você queira registrar manualmente...
    ])->create();
