<?php
namespace App\Helpers;
use Illuminate\Support\Arr;
class UploadFileValidate
{
    public static function checkExtension($fileOriginalExt)
    {
        foreach (FILE_EXT as $ext) {
            if (strcasecmp($fileOriginalExt, $ext) == 0) {
                return $ext;
            }
        }
        return false;
    }
    public static function checkMime($fileOriginalMime)
    {
        foreach (FILE_MIMES as $mime) {
            if (strcasecmp($fileOriginalMime, $mime) == 0 || (strpos($mime, $fileOriginalMime) !== false && $mime == FILE_MIMES[5])) {
                return $mime;
            }
        }
        return false;
    }
    public static function checkIfImage($fileOriginExt)
    {
        foreach (Arr::except(FILE_EXT, [0, 5]) as $ext) {
            if (strcasecmp($fileOriginExt, $ext) == 0) {
                return $ext;
            }
        }
        return false;
    }
    function clean($string) {
        $string = str_replace(' ', '-', $string); 
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); 
    }
}
