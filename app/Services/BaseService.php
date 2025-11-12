<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class BaseService
{
    public $responseCode = 200, $responseMsg, $responseData = null, $line = null;

    protected function executeFunction(callable $function) {
        try {
            DB::beginTransaction();
            $data = call_user_func($function);
            DB::commit();

            return $this->apiResponse(200, 'Good', $data, 200, 'Good', null);
        } catch (Exception $e) {
            DB::rollback();
            return $this->apiResponse(500, 'Failed', null, 500, 'DB', $e->getMessage());
        }
    }
    protected function apiResponse($code, $message, $result = null, $httpCode = 200, $errorMessage = null, $line = null, $latency = null) {
        if ($httpCode === 200) {
            return response()->json([
                    "code" => $code,
                    "message" => $message,
                    "data" => $result,
                    'meta' => [
                        'current_page' => $result->currentPage(),
                        'per_page' => $result->perPage(),
                        'total' => $result->total(),
                        'last_page' => $result->lastPage(),
                        'latency' => microtime(true) - $latency
                    ],
                ],
                $httpCode
            );
        }

        return response()->json(
            [
                "code" => $code,
                "message" => $message,
                "error" => [
                    "message" => $line,
                    "data" => $result
                ]
            ],
            $httpCode
        );
    }
    // public function exceptionResponse($exception, $module) {
    //     return $this->response(
    //         $exception->getCode(),
    //         "Unable to process your transaction at this moment",
    //         null,
    //         422,
    //         $module,
    //         $exception->getMessage(),
    //         10
    //     );
    // }
}
