<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;

trait ApiResponse
{
    // RESTful API response
    public function moduleResponse($response, int $status = 200): JsonResponse
    {
        if (isset($response['data'])) {
            $response = $response['data'];
        }

        return match ($status) {
            200 => $this->success($response),
            201 => $this->created($response),
            204 => $this->noContent(),
            400 => $this->badRequest(),
            401 => $this->serviceUnavailable(),
            403 => $this->forbidden(),
            404 => $this->notFound($response),
            405 => $this->methodNotAllowed(),
            429 => $this->tooManyRequests(),
            500 => $this->serverError(),

            default => response()->json($response['data'], $status),
        };
    }

    public function notFound($message = 'Not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    public function error($message = '', $code = 400): JsonResponse
    {
        return $this->apiResponse($message, $code);
    }

    public function apiResponse($data = [], $status = 200): JsonResponse
    {
        // if data is paginated, return paginated data
        if ($data instanceof Paginator) {
            $data = $data->toArray();
            $data['data'] = $data['data'] ?? [];
            $data['meta'] = [
                'per_page' => $data['per_page'] ?? 0,
                'current_page' => $data['current_page'] ?? 0,
                'from' => $data['from'] ?? 0,
                'to' => $data['to'] ?? 0,
            ];
            $data['paginate'] = 1;
        } else {
            $data = [
                'data' => $data,
            ];
        }

        $data['status'] = $status;

        if ($status >= 200 && $status <= 299) {
            $data['success'] = 1;
        } else {
            $data['success'] = 0;
        }

        return response()->json($data, $status)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function forbidden($message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    public function unauthorized($message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    public function badRequest($message = 'Bad request'): JsonResponse
    {
        return $this->error($message, 400);
    }

    // bad request

    public function created($message = 'Created'): JsonResponse
    {
        return $this->success($message, 201);
    }

    // created

    public function success($data = []): JsonResponse
    {
        return $this->apiResponse($data, 200);
    }

    // accepted

    public function accepted($message = 'Accepted'): JsonResponse
    {
        return $this->success($message, 202);
    }

    // no content
    public function noContent($message = 'No content'): JsonResponse
    {
        return $this->success($message, 204);
    }

    // updated
    public function updated($message = 'Updated'): JsonResponse
    {
        return $this->success($message, 200);
    }

    // deleted
    public function deleted($message = 'Deleted'): JsonResponse
    {
        return $this->success($message, 200);
    }

    // not allowed
    public function notAllowed($message = 'Not allowed'): JsonResponse
    {
        return $this->error($message, 405);
    }

    // conflict
    public function conflict($message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409);
    }

    // too many requests
    public function tooManyRequests($message = 'Too many requests'): JsonResponse
    {
        return $this->error($message, 429);
    }

    // server error
    public function serverError($message = 'Server error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    // service unavailable
    public function serviceUnavailable($message = 'Service unavailable'): JsonResponse
    {
        return $this->error($message, 503);
    }

    // method not allowed
    public function methodNotAllowed($message = 'Method not allowed'): JsonResponse
    {
        return $this->error($message, 405);
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
