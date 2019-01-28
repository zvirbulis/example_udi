<?php


namespace App\Udi\Http;

use App\Udi\Exceptions\UdiDuplicateEntryException;
use App\Udi\Exceptions\UdiException;
use App\Udi\Exceptions\UdiForeignKeysDeleteException;
use App\Udi\Exceptions\UdiNotFoundException;
use App\Udi\Exceptions\UdiProgressActionException;
use App\Udi\Exceptions\UdiWorkspaceResourceException;
use ClassesWithParents\E;

class Response
{
    public static function ok(array $data, $message = 'OK')
    {
        return self::response(200, 'success', $message, $data);
    }

    public static function created(array $data, $message = 'Created', $location)
    {
        return self::response(201, 'success', $message, $data)
            ->header('Location', $location);
    }

    public static function exception(\Exception $e)
    {
        if ($e instanceof UdiNotFoundException) {
            return self::notFound([], $e->getMessage());
        } elseif ($e instanceof UdiWorkspaceResourceException) {
            return self::badRequest([], $e->getMessage());
        }
        elseif($e instanceof UdiProgressActionException){
            return self::ok($e->getResponseData());
        }
        elseif ($e instanceof UdiException) {
            return self::badRequest([], $e->getMessage());
        } elseif (!env('APP_DEBUG')) {
            return self::fail([], $e->getMessage());
        }

        throw $e;
    }

    public static function badRequest(array $data = [], $message = 'Bad request')
    {
        return self::response(400, 'error', $message, $data);
    }

    public static function unauthorized(array $data = [], $message = 'Unauthorized')
    {
        return self::response(401, 'error', $message, $data);
    }

    public static function forbidden(array $data = [], $message = 'Forbidden')
    {
        return self::response(403, 'error', $message, $data);
    }

    public static function notFound(array $data = [], $message = 'Not found')
    {
        return self::response(404, 'error', $message, $data);
    }

    public static function fail(array $data = [], $message = 'Internal server error')
    {
        return self::response(500, 'fail', $message, $data);
    }

    protected static function response($code, $status, $message = '', array $data = [])
    {
        return \Illuminate\Support\Facades\Response::json([
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
