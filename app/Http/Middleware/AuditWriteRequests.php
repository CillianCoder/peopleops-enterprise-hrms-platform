<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final readonly class AuditWriteRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $startedAt = microtime(true);

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->record($request, $user, $startedAt, $this->statusForException($exception), 'exception');

            throw $exception;
        }

        if ($user === null || $request->isMethodSafe()) {
            return $response;
        }

        $this->record($request, $user, $startedAt, $response->getStatusCode(), 'response');

        return $response;
    }

    private function record(Request $request, mixed $user, float $startedAt, int $statusCode, string $outcome): void
    {
        if ($user === null || $request->isMethodSafe()) {
            return;
        }

        try {
            activity('requests')
                ->causedBy($user)
                ->event('request_completed')
                ->withProperties([
                    'method' => $request->method(),
                    'path' => '/'.$request->path(),
                    'route_name' => $request->route()?->getName(),
                    'status_code' => $statusCode,
                    'outcome' => $outcome,
                    'validation_errors' => $request->hasSession() && $request->session()->has('errors'),
                    'ip' => $request->ip(),
                    'user_agent' => str((string) $request->userAgent())->limit(180)->toString(),
                    'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ])
                ->log('Authenticated write request completed.');
        } catch (Throwable $exception) {
            Log::warning('Audit request logging failed.', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function statusForException(Throwable $exception): int
    {
        if ($exception instanceof ValidationException) {
            return 422;
        }

        if ($exception instanceof AuthorizationException) {
            return 403;
        }

        if ($exception instanceof AuthenticationException) {
            return 401;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
