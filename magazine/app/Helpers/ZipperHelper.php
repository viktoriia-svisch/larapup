<?php
namespace App\Helpers;
use ZipArchive;
class ZipperHelper
{
    public static function createZip($directoryOutput, $listFilesDirectory, $prefix = '', $encryptionPassword = null)
    {
        $zipFile = new ZipArchive();
        $status = $zipFile->open($directoryOutput, ZipArchive::CREATE);
        if ($status !== true) return false;
        foreach ($listFilesDirectory as $file) {
            $zipFile->addFile($file, $prefix .basename($file));
        }
        if ($encryptionPassword !== null) {
            $zipFile->setEncryptionName(basename($directoryOutput), ZipArchive::EM_AES_256, $encryptionPassword);
            $zipFile->setPassword($encryptionPassword);
        }
        $zipFile->close();
        return $directoryOutput;
    }
    public static function isEmpty($directoryToZip)
    {
        $zipFile = new ZipArchive();
        $status = $zipFile->open($directoryToZip);
        if ($status !== true) {
            return $zipFile->numFiles > 0;
        }
        return false;
    }
}
