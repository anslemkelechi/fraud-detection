<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Respond with a success JSON structure.
     *
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithSuccess($data, $message)
    {
        return response()->json(['status' => "success", "message" => $message, 'data' => $data]);
    }

    /**
     * Respond with an error JSON structure.
     *
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message, $status = 400)
    {
        return response()->json(['status' => "failed", 'message' => $message], $status);
    }
}
