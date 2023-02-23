<?php
namespace App\Helpers;
use ZipArchive;
class ZipperHelper
{
    public static function createZip($directoryOutput, $listFilesDirectory, $encryptionPassword = null){
        $zipFile = new ZipArchive();
        $status = $zipFile->open($directoryOutput, ZipArchive::CREATE);
        if (!$status) return false;
        foreach ($listFilesDirectory as $file){
            $zipFile->addFile($file, basename($file));
        }
        if ($encryptionPassword !== null){
            $zipFile->setEncryptionName(basename($directoryOutput), ZipArchive::EM_AES_256, $encryptionPassword);
            $zipFile->setPassword($encryptionPassword);
        }
        $zipFile->close();
        return $directoryOutput;
    }
}
