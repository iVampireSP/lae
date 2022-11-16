<?php

namespace App\Helpers;

trait ApiResponse
{
    // RESTful API response
    public function remoteResponse($response, $status = 200)
    {
        return response()->json($response, $status)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function notFound($message = 'Not found')
    {
        return $this->error($message, 404);
    }

    // success

    public function error($message = '', $code = 400)
    {
        return $this->apiResponse($message, $code);
    }

    // error

    public function apiResponse($data = [], $status = 200)
    {
        // if data is paginated, return paginated data
        if ($data instanceof \Illuminate\Pagination\Paginator) {
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


    // not found

    public function forbidden($message = 'Forbidden')
    {
        return $this->error($message, 403);
    }

    // forbidden

    public function unauthorized($message = 'Unauthorized')
    {
        return $this->error($message, 401);
    }

    // unauthorized

    public function badRequest($message = 'Bad request')
    {
        return $this->error($message, 400);
    }

    // bad request

    public function created($message = 'Created')
    {
        return $this->success($message, 201);
    }

    // created

    public function success($data = [])
    {
        return $this->apiResponse($data, 200);
    }

    // accepted

    public function accepted($message = 'Accepted')
    {
        return $this->success($message, 202);
    }

    // no content
    public function noContent($message = 'No content')
    {
        return $this->success($message, 204);
    }

    // updated
    public function updated($message = 'Updated')
    {
        return $this->success($message, 200);
    }

    // deleted
    public function deleted($message = 'Deleted')
    {
        return $this->success($message, 200);
    }

    // not allowed
    public function notAllowed($message = 'Not allowed')
    {
        return $this->error($message, 405);
    }

    // conflict
    public function conflict($message = 'Conflict')
    {
        return $this->error($message, 409);
    }

    // too many requests
    public function tooManyRequests($message = 'Too many requests')
    {
        return $this->error($message, 429);
    }

    // server error
    public function serverError($message = 'Server error')
    {
        return $this->error($message, 500);
    }

    // service unavailable
    public function serviceUnavailable($message = 'Service unavailable')
    {
        return $this->error($message, 503);
    }

    // method not allowed
    public function methodNotAllowed($message = 'Method not allowed')
    {
        return $this->error($message, 405);
    }

    // not acceptable
    public function notAcceptable($message = 'Not acceptable')
    {
        return $this->error($message, 406);
    }

    // precondition failed
    public function preconditionFailed($message = 'Precondition failed')
    {
        return $this->error($message, 412);
    }
}
