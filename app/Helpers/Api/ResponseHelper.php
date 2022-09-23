<?php

namespace App\Helpers\Api;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Prepare success response
     *
     * @param string $apiStatus
     * @param string $apiMessage
     * @param array $apiData
     * @param bool $convertNumeric - To specify whether to transform number strings into int type
     * @return Illuminate\Http\JsonResponse
     */
    public function success(
        string $apiStatus = '',
        string $apiMessage = '',
        array $apiData = [],
        bool $convertNumeric = false
    ): JsonResponse {
        $response['status'] = $apiStatus;

        // if (!empty($apiData)) {
        $response['data'] = $apiData;
        // }

        if ($apiMessage) {
            $response['message'] = $apiMessage;
        }

        return response()->json($response, $apiStatus, [], $convertNumeric ? JSON_NUMERIC_CHECK : null);
    }

    /**
     * Prepare success response
     *
     * @param string $apiStatus
     * @param string $apiMessage
     * @param Illuminate\Pagination\LengthAwarePaginator $apiData
     * @param array $metaData
     * @param array $customFields
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function successWithPagination(
        string $apiStatus = '',
        string $apiMessage = '',
        LengthAwarePaginator $apiData = null,
        array $metaData = [],
        bool $convertNumeric = true,
        array $customFields = []
    ): JsonResponse {
        $response['status'] = $apiStatus;
        $response['data'] = [];

        // Check response data have pagination or not? Pagination response parameter sets
        if ($apiData->count()) {
            $apiData->appends(['perPage' => $apiData->perPage()]);

            $response['data'] = $apiData->toArray()['data'];

            $response['pagination'] = [
                "total" => $apiData->total(),
                "per_page" => $apiData->perPage(),
                "current_page" => $apiData->currentPage(),
                "total_pages" => $apiData->lastPage(),
                "next_url" => $apiData->nextPageUrl()
            ];
        }

        // Append set custom fields data
        foreach ($customFields as $field) {
            if ($apiData->$field ?? false) {
                $response[$field] = $apiData->$field;
            }
        }

        if (!empty($metaData)) {
            $response['meta_data'] = $metaData;
        }

        if ($apiMessage) {
            $response['message'] = $apiMessage;
        }

        return response()->json($response, $apiStatus, [], $convertNumeric ? JSON_NUMERIC_CHECK : null);
    }

    /**
     * Prepare error response
     *
     * @param string $statusCode
     * @param string $customErrorMessage
     * @return Illuminate\Http\JsonResponse
     */
    public static function error(
        string $statusCode = '',
        string $customErrorMessage = ''
    ): JsonResponse {
        $response['status'] = $statusCode;
        $response['message'] = $customErrorMessage;

        $data["errors"][] = $response;
        return response()->json($data, $statusCode, [], JSON_NUMERIC_CHECK);
    }

    /* Upload images*/
    public static function uploadImage($image)
    {
        $name = $image->getClientOriginalName(); //file.jpg
        $file_name = trim(pathinfo($name, PATHINFO_FILENAME)); // file
        $extension = pathinfo($name, PATHINFO_EXTENSION); // jpg
        $imageName = $file_name . '_' . time() . '.' . $extension;
        $image->storeAs('public/uploads', $imageName);
        return $imageName;
    }
}
