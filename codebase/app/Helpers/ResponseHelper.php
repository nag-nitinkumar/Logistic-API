<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Lang;

/**
 * Class to format response as per need
 */
class ResponseHelper
{

    /**
     * sendError generate api response as error with given messages and code
     * @param  string $messageCode      Error message to be displayed for user
     * @param  int $responseCode valid Http error code to be respond
     * @return json response for user
     */
    public function sendError($messageCode, $responseCode = JsonResponse::HTTP_BAD_REQUEST)
    {

        //get error message from localize file if key exist else show given error
        $errorMessage = $this->getLocaleMessage($messageCode);

        $response = ['error' => $errorMessage];
        return response()->json($response, $responseCode);
    }


    public function sendSuccess($messageCode, $responseCode = JsonResponse::HTTP_OK, $responseData = null)
    {
        //if have some response then send it directly
        if ($responseData !== null) {
            return response()->json($responseData, $responseCode);
        } else {
            //get message from localize file if key exist else show given error
            $successMessage = $this->getLocaleMessage($messageCode);
            $response = ['status' => $successMessage];
            return response()->json($response, $responseCode);
        }
    }
    
    public function getLocaleMessage($key)
    {
        return Lang::has('message.'.$key)?__('message.'.$key):$key;
    }
}
