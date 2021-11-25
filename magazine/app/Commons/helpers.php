<?php
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
if (!function_exists('cdn_asset')) {
    function cdn_asset($path)
    {
        return \App\Utils::cdnAsset($path);
    }
}
if (!function_exists('trustedproxy_config')) {
    function trustedproxy_config($key, $env_value)
    {
        if ($key === 'proxies') {
            if ($env_value === '*' || $env_value === '**') {
                return $env_value;
            }
            return $env_value ? explode(',', $env_value) : null;
        } elseif ($key === 'headers') {
            if ($env_value === 'HEADER_X_FORWARDED_AWS_ELB') {
                return Request::HEADER_X_FORWARDED_AWS_ELB;
            } elseif ($env_value === 'HEADER_FORWARDED') {
                return Request::HEADER_FORWARDED;
            }
            return Request::HEADER_X_FORWARDED_ALL;
        }
        return null;
    }
}
if (!function_exists('redirect_back_field')) {
    function redirect_back_field()
    {
        return new HtmlString('<input type="hidden" name="_redirect_back" value="' . old('_redirect_back', back()->getTargetUrl()) . '">');
    }
}
if (!function_exists('redirect_back_to')) {
    function redirect_back_to($callbackUrl = null, $status = 302, $headers = [], $secure = null)
    {
        $to = request()->input('_redirect_back', back()->getTargetUrl());
        if ($callbackUrl && !starts_with($to, $callbackUrl)) {
            $to = $callbackUrl;
        }
        return redirect($to, $status, $headers, $secure);
    }
}
if (!function_exists('asset_ver')) {
    function asset_ver($path, $secure = null)
    {
        return get_asset_ver($path, $secure);
    }
}
if (!function_exists('get_asset_ver')) {
    function get_asset_ver($path, $secure = null, $asset_link = false)
    {
        $url = $asset_link ? $path : asset($path, $secure);
        return $url . '?v=' . (config('app.debug') ? date('Ymdhis') : date('Y'));
    }
}
if (!function_exists('__l')) {
    function __l($key, $attr = [])
    {
        if (\Lang::has("messages.$key")) {
            return __("messages.$key", $attr);
        } else {
            \Log::info("messages.$key not exists");
            return $key;
        }
    }
}
