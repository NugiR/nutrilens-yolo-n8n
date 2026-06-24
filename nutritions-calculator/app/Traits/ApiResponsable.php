<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponsable
{
    protected function success(mixed $data = null, string $message = 'Ok', int $code = 200): JsonResponse
    {
        $body = ['code' => $code, 'message' => $message];

        if ($data instanceof LengthAwarePaginator) {
            $body['data'] = $data->items();
            $body['meta'] = [
                'page'      => $data->currentPage(),
                'limit'     => $data->perPage(),
                'total'     => $data->total(),
                'last_page' => $data->lastPage(),
            ];
        } else {
            $body['data'] = $data;
        }

        return response()->json($body, $code);
    }

    protected function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $body = ['code' => $code, 'message' => $message];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $code);
    }
}
