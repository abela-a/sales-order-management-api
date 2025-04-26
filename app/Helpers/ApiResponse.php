<?php

namespace App\Helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiResponse
{
    public static function rollback($error, $message = 'Oops! Process not completed')
    {
        DB::rollBack();

        self::throw($error, $message);
    }

    public static function notFound($message = 'Resource not found')
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }

    public static function throw($error, $message = 'Sorry, something went wrong')
    {
        Log::error($error);

        throw new HttpResponseException(response()->json(['message' => $message], 500));
    }

    public static function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'data' => $result,
        ];

        if (! empty($message)) {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }
}
