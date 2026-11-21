<?php

/**
 * An "expected" error with an HTTP status code, thrown by services.
 * Examples:
 *   throw new ApiException('Product not found', 404);
 *   throw new ApiException('Validation failed', 400, ['price' => 'Price must be 0 or more.']);
 *
 * The error handler in index.php turns it into a JSON response with that status code.
 */
class ApiException extends Exception
{
    private $statusCode;
    private $details;

    public function __construct($message, $statusCode = 400, $details = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /** Field-level errors, e.g. ['email' => 'Email is not valid.'] */
    public function getDetails()
    {
        return $this->details;
    }
}
