<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
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
            401, 502 => $this->serviceUnavailable(),
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

    public function serviceUnavailable($message = 'Service unavailable'): JsonResponse
    {
        return $this->error($message, 503);
    }

    public function forbidden($message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    public function notFound($message = 'Not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    public function methodNotAllowed($message = 'Method not allowed'): JsonResponse
    {
        return $this->error($message, 405);
    }

    public function tooManyRequests($message = 'Too many requests'): JsonResponse
    {
        return $this->error($message, 429);
    }

    public function serverError($message = 'Server error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    public function failed($message = 'Failed', $code = 400): JsonResponse
    {
        return $this->apiResponse($message, $code);
    }

    public function unauthorized($message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    public function accepted($message = 'Accepted'): JsonResponse
    {
        return $this->success($message, 202);
    }

    public function updated(mixed $message = 'Updated'): JsonResponse
    {
        if ($message instanceof Model) {
            $message = $message->getChanges();
        }

        return $this->success($message);
    }

    public function deleted($message = 'Deleted'): JsonResponse
    {
        return $this->success($message, 204);
    }

    public function notAllowed($message = 'Not allowed'): JsonResponse
    {
        return $this->error($message, 405);
    }

    public function conflict($message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409);
    }

    public function notAcceptable($message = 'Not acceptable'): JsonResponse
    {
        return $this->error($message, 406);
    }

    public function preconditionFailed($message = 'Precondition failed'): JsonResponse
    {
        return $this->error($message, 412);
    }
}
