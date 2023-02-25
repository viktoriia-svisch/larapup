<?php
if (! function_exists('config_path')) {
    function config_path($path = '')
    {
        return app()->basePath('config').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
