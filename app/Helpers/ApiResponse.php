<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    // REST API response
    public function moduleResponse($response, int $status = 200): JsonResponse
    {
        return match ($status) {
            200 => $this->success($response),
            201 => $this->created($response),
            204 => $this->noContent(),
            400 => $this->badRequest($response),
            401 => $this->serviceUnavailable(),
            403 => $this->forbidden($response),
            404 => $this->notFound($response),
            405 => $this->methodNotAllowed(),
            429 => $this->tooManyRequests(),
            500 => $this->serverError($response),

            default => response()->json($response, $status),
        };
    }

    public function success($data = [], $status = 200): JsonResponse
    {
        return $this->apiResponse($data, $status);
    }

    public function apiResponse($data = [], $status = 200): JsonResponse
    {
        // if data is string, then it is error message
        if (is_string($data)) {
            $data = [
                'message' => $data,
            ];
        }

        return response()->json($data, $status)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function created($message = 'Created'): JsonResponse
    {
        return $this->success($message, 201);
    }

    public function noContent($message = 'No content'): JsonResponse
    {
        return $this->success($message, 204);
    }

    public function badRequest($message = 'Bad request'): JsonResponse
    {
        return $this->error($message);
    }

    public function error($message = '', $code = 400): JsonResponse
    {
        return $this->apiResponse($message, $code);
    }

    // bad request

    public function serviceUnavailable($message = 'Service unavailable'): JsonResponse
    {
        return $this->error($message, 503);
    }

    // created

    public function forbidden($message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    // accepted

    public function notFound($message = 'Not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    // no content

    public function methodNotAllowed($message = 'Method not allowed'): JsonResponse
    {
        return $this->error($message, 405);
    }

    // updated

    public function tooManyRequests($message = 'Too many requests'): JsonResponse
    {
        return $this->error($message, 429);
    }

    // deleted

    public function serverError($message = 'Server error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    // not allowed

    public function unauthorized($message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    // conflict

    public function accepted($message = 'Accepted'): JsonResponse
    {
        return $this->success($message, 202);
    }

    // too many requests

    public function updated($message = 'Updated'): JsonResponse
    {
        return $this->success($message, 200);
    }

    // server error

    public function deleted($message = 'Deleted'): JsonResponse
    {
        return $this->success($message, 200);
    }

    // service unavailable

    public function notAllowed($message = 'Not allowed'): JsonResponse
    {
        return $this->error($message, 405);
    }

    // method not allowed

    public function conflict($message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409);
    }

    // not acceptable

    public function notAcceptable($message = 'Not acceptable'): JsonResponse
    {
        return $this->error($message, 406);
    }

    // precondition failed
    public function preconditionFailed($message = 'Precondition failed'): JsonResponse
    {
        return $this->error($message, 412);
    }
}
