<?php
namespace App\Http\Controllers;

abstract class Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): \Illuminate\Http\JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== null) $body['errors'] = $errors;
        return response()->json($body, $status);
    }
}
