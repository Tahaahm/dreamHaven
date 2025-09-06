<?php

namespace App\Helper;

class ResponseDetails
{
    const STATUS_TRUE = true;
    const STATUS_FALSE = false;

    // Updated to use valid HTTP status codes
    const CODE_SUCCESS = 200;
    const CODE_FAILURE = 500;  // General failure code
    const CODE_SERVER_ERROR = 500; // Specific 500 code for server errors
    const CODE_UNAUTHORIZED = 401;
    const CODE_NOT_FOUND = 404;
    const CODE_VALIDATION_ERROR = 400;

    public static function successMessage($customMessage = null)
    {
        return $customMessage ?? 'Operation successful';
    }

    public static function errorMessage($customMessage = null)
    {
        return $customMessage ?? 'Operation failed';
    }

    public static function unauthorizedMessage()
    {
        return 'Unauthorized access';
    }

    public static function notFoundMessage($customMessage = null)
    {
        return $customMessage ?? 'Resource not found';
    }

    public static function validationErrorMessage()
    {
        return 'Validation failed';
    }
}
