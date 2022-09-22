<?php
namespace App\Helpers;
class UploadFileValidate
{
    public static function checkExtension($fileOriginalExt)
    {
        foreach (FILE_EXT as $ext) {
            if (strcasecmp($fileOriginalExt, $ext) == 0) {
                return true;
            }
        }
        return false;
    }
    public static function checkMime($fileOriginalMime)
    {
        foreach (FILE_MIMES as $mime) {
            if (strcasecmp($fileOriginalMime, $mime) == 0) {
                return true;
            }
        }
        return false;
    }
}
