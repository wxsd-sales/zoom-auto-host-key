<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Response;
//use Inertia\Inertia;
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
    }

//    /**
//     * Prepare exception for rendering.
//     *
//     * @param $request
//     * @param Throwable $e
//     *
//     * @return JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
//     * @throws Throwable
//     */
//    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
//    {
//        $response = parent::render($request, $e);
//
//        if (! app()->environment(['local', 'testing']) && in_array($response->status(), [500, 503, 404, 403])) {
//            return Inertia::render('Error', ['status' => $response->status()])
//                ->toResponse($request)
//                ->setStatusCode($response->status());
//        } elseif ($response->status() === 419) {
//            return back()->with([
//                'message' => 'The page expired, please try again.',
//            ]);
//        }
//
//        return $response;
//    }
}
