<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        }

        // For all other exceptions in production, show a custom 500 error page
        if (!config('app.debug')) {
            return $this->renderCustomErrorPage($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render the given HttpException.
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $statusCode = $e->getStatusCode();

        // Check if a view exists for this specific error code
        if (view()->exists("errors.{$statusCode}")) {
            return response()->view("errors.{$statusCode}", [
                'exception' => $e,
            ], $statusCode);
        }

        // If no specific view exists, fallback to the default handler
        return parent::renderHttpException($e);
    }

    /**
     * Render a custom error page for all non-HTTP exceptions.
     *
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response
     */
    protected function renderCustomErrorPage(Throwable $e)
    {
        return response()->view('errors.500', [
            'exception' => $e,
        ], 500);
    }
}
