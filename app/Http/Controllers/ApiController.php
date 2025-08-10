<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * Return a JSON response
     */
    protected function jsonResponse($data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Return a success response
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->jsonResponse($response, $status);
    }

    /**
     * Return an error response
     */
    protected function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->jsonResponse($response, $status);
    }

    /**
     * Return a not found response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Return a validation error response
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return a created response
     */
    protected function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a no content response
     */
    protected function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }

    /**
     * Return paginated data
     */
    protected function paginated(array $items, int $total, int $page = 1, int $perPage = 15): JsonResponse
    {
        $lastPage = ceil($total / $perPage);
        
        return $this->jsonResponse([
            'data' => $items,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total)
            ]
        ]);
    }
}
