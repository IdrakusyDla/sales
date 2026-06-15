<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
        then: function () {
            \Illuminate\Support\Facades\Route::middleware("web")->group(
                base_path("routes/auth.php"),
            );
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            append: [
                \App\Http\Middleware\TrackActivity::class,
                \App\Http\Middleware\CheckActiveUser::class,
                \Illuminate\Session\Middleware\AuthenticateSession::class,
            ],
        );
        $middleware->trustProxies(at: "*");

        // Register alias untuk middleware Role & Permission
        $middleware->alias([
            "role" => \App\Http\Middleware\CheckRole::class,
            "permission" => \App\Http\Middleware\CheckPermission::class,
            "force.password.change" =>
                \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            \Illuminate\Session\TokenMismatchException $e,
            $request,
        ) {
            // Kalau token expired saat logout, langsung logout saja
            if ($request->is("logout")) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect("/");
            }

            if ($request->expectsJson()) {
                return response()->json(
                    [
                        "message" =>
                            "Sesi telah berakhir. Silakan refresh halaman.",
                    ],
                    419,
                );
            }

            return redirect()
                ->back()
                ->withInput(
                    $request->except(
                        "_token",
                        "password",
                        "password_confirmation",
                    ),
                )
                ->with("error", "Sesi telah berakhir. Silakan coba lagi.");
        });
    })
    ->create();
