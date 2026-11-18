<?php

use App\Http\Middleware\CheckModulePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'preventBackHistory' => App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        ## UNAUTHORIZED 401
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            return response()->view('errors.401', [
                'judul' => 'Unauthorized!',
                'exception' => $e->getMessage(),
                'status' => 401
            ], 401);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->view('errors.404', [
                'judul' => 'Not Found!',
                'message' => 'Record not found.',
                'status' => 404
            ], 404);
        });

        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'error' => 'Halaman kadaluarsa, silahkan coba lagi.',
                ]);
            }
            return $response;
        });
    })->create();
