<?php


namespace App\Utilities;

use Illuminate\Http\Response;

/**
 * Class ServiceResponse
 * @package App\Utilities
 */
class ServiceResponse
{

    /**
     * @param $message
     * @param string $type
     * @return array
     */
    public static function notification($message, $type = 'success')
    {
        return [
            [
                'type'    => $type,
                'message' => $message
            ]
        ];
    }

    /**
     * @param $message
     * @return array
     */
    public static function error($message)
    {
        $data = [
            'message'   => $message,
            'status'    => false,
            'type'      => 'danger',
            'data'      => []
        ];

        \Session::flash('notifications', [$data]);

        return $data;
    }

    /**
     * @param $message
     * @param null $data
     * @return array
     */
    public static function success($data = null, $message = '')
    {
        return [
            'message'   => $message,
            'status'    => true,
            'data'      => $data
        ];
    }

    /**
     * @param $message
     * @param int $code
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function jsonNotification($message, $code = 200, $data = [])
    {
        $status = self::toHttpStatus($code);

        return response()->json(
            [
                'message'   => $message,
                'code'      => $status,
                'data'      => $data,
                'status'    => $status
            ]
        )->setStatusCode($status);
    }

    /**
     * @param mixed $code
     * @return int
     */
    private static function toHttpStatus($code)
    {
        if (! is_numeric($code)) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $status = (int) $code;

        return $status >= 100 && $status <= 599
            ? $status
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

}
