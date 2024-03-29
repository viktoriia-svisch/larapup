<?php
namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    function get_userIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    function get_userXForwardFor()
    {
        return !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
    }
    function responseCustom($data= null, $links = null, $meta = null)
    {
        return response()->json([
            'data' => $data,
            'links' => $links,
            'meta' => $meta
        ]);
    }
    function responseEmpty()
    {
        return response()->json([
            'data' => null,
            'links' => null,
            'meta' => null
        ]);
    }
    function responseBladeMessage($message, $is_success = true, $custom_key = 'action_response', $custom_status_key = 'status_ok', $custom_message_key = 'status_message'){
        return [
            $custom_key => [
                $custom_status_key => $is_success,
                $custom_message_key => $message
            ]
        ];
    }
    function responseMessage($message, $error = false, $status = 'success', $callback = null)
    {
        $statusFinal = $status !== 'success' && $status !== 'failed' ? $status :
            $error ? 'failed' : 'success';
        return response()->json([
            'error' => $error,
            'status' => $statusFinal,
            'message' => $message,
            'callback' => $callback,
        ]);
    }
    function responseUnauthenticated()
    {
        return $this->responseMessage('User is not authenticated', true);
    }
    function currentTime()
    {
        return Carbon::now('utc')->toDateTimeString();
    }
    function isValidNumeric($any, $strict= false){
        if (is_numeric($any)){
            if ($strict){
                if (+$any > 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    function isAuthenticated($guard = STUDENT_GUARD)
    {
        return auth()->guard($guard)->check();
    }
    function random_str($length)
    {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            try {
                $pieces [] = $keyspace[random_int(0, $max)];
            } catch (\Exception $e) {
            }
        }
        return implode('', $pieces);
    }
}
