<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Custom exception for course-related operations
 */
class CourseException extends Exception
{
    /**
     * Create a new course exception instance
     */
    public function __construct(string $message = 'Course operation failed', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        // Log the exception or send to external service
        Log::error('Course Exception: ' . $this->getMessage(), [
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString()
        ]);
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Course operation failed',
                'message' => $this->getMessage()
            ], 422);
        }

        return redirect()->back()
            ->withErrors(['course' => $this->getMessage()])
            ->withInput();
    }
}