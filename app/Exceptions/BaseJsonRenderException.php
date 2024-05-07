<?php


namespace App\Exceptions;


use App\Utilities\ServiceResponse;
use Exception;
use Log;

class BaseJsonRenderException extends Exception
{
    /**
     * log error.
     */
    public function report()
    {
        Log::error('Base json render exception.', [
            'message'   => $this->getMessage(),
            'code'      => $this->getCode(),
            'line'      => $this->getLine(),
            'file'      => $this->getFile(),
            'data'      => request()->all()
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        return ServiceResponse::jsonNotification($this->getMessage(), $this->getCode(), []);
    }
}
