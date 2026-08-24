<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Dibatasi pada prefix `api/` (bukan `api/v1/`) agar versi mendatang ikut
        // terformat, dan agar 404 atas versi yang salah tetap berupa JSON.
        $this->renderable(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return $this->renderApiException($e);
        });
    }

    private function renderApiException(Throwable $e): \Illuminate\Http\JsonResponse
    {
        [$status, $code, $message, $details] = match (true) {
            $e instanceof ValidationException => [
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'validation_failed',
                $e->validator->errors()->first(),
                $e->errors(),
            ],
            $e instanceof AuthenticationException => [
                Response::HTTP_UNAUTHORIZED,
                'unauthenticated',
                'API key tidak valid atau tidak disertakan.',
                null,
            ],
            $e instanceof TooManyRequestsHttpException => [
                Response::HTTP_TOO_MANY_REQUESTS,
                'rate_limited',
                'Terlalu banyak permintaan. Coba lagi nanti.',
                null,
            ],
            $e instanceof NotFoundHttpException => [
                Response::HTTP_NOT_FOUND,
                'not_found',
                'Endpoint tidak ditemukan. Pastikan versi API disertakan, misalnya /api/v1/jadwal-majelis.',
                null,
            ],
            default => [
                $e instanceof HttpExceptionInterface ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR,
                'server_error',
                config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server.',
                null,
            ],
        };

        $payload = ['error' => array_filter([
            'code' => $code,
            'message' => $message,
            'details' => $details,
        ], fn ($value) => $value !== null)];

        $response = response()->json($payload, $status);

        if ($e instanceof HttpExceptionInterface) {
            $response->withHeaders($e->getHeaders());
        }

        return $response;
    }
}
