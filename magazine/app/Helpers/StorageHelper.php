<?php
namespace App\Helpers;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class StorageHelper
{
    const TYPES = [
        'ARTICLE' => 1,
        'COMMENT' => 2,
        'PROFILE' => 3,
        'PUBLISH' => 4,
        'AVATAR' => 5,
        "BACKUP" => 6
    ];
    const TYPE_NAMES = [
        1 => 'articles',
        2 => 'comments',
        3 => 'profiles',
        4 => 'publishes',
        5 => 'avatar',
        6 => 'backup',
    ];
    public static function savePublishFileSubmission($idFacultySemester, $idPublish, UploadedFile $file, &$filePath = null)
    {
        $fileName = Carbon::now()->toDateString() . '-' . Str::random(6) . '-' . $file->getClientOriginalName();
        $filePath = self::getPublishFilePath($idFacultySemester, $idPublish);
        self::save($file, $filePath, $fileName);
        return [
            "full" => $filePath . $fileName,
            "file" => $fileName
        ];
    }
    public static function getPublishFilePath($idFacultySemester, $idPublish, $path = '', $strict = false)
    {
        $folderPath = self::getTypeFolder(self::TYPES['PUBLISH'], $strict) . $idPublish . '/fs/' . $idFacultySemester . '/';
        return $folderPath . $path;
    }
    public static function getTypeFolder($type, $strict = true)
    {
        if (!isset(self::TYPE_NAMES[$type]))
            throw new Exception('type is undefined');
        if ($strict) {
            return 'data/local/' . self::TYPE_NAMES[$type] . '/';
        }
        return 'public/data/local/' . self::TYPE_NAMES[$type] . '/';
    }
    public static function save($file, $path, $fileName = '')
    {
        if (!$fileName) {
            $fileName = $file->getClientOriginalName();
        }
        self::disk()->putFileAs($path, $file, $fileName);
    }
    private static function disk()
    {
        return Storage::disk('local');
    }
    public static function getTemporaryBackupFacultySemesterPath($faculty_id, $semester_id, $fileName = '', $strict = true)
    {
        $folderPath = self::getTypeFolder(self::TYPES['ARTICLE'], $strict) . 'backup/' . $faculty_id . '/' . $semester_id . '/';
        return $folderPath . $fileName;
    }
    public static function getTemporaryBackupSemesterPath($semester_id, $fileName = '', $strict = true)
    {
        $folderPath = self::getTypeFolder(self::TYPES['BACKUP'], $strict) . 'semester/' . $semester_id . '/';
        return $folderPath . $fileName;
    }
    public static function deletePublishFile($idFacultySemester, $idPublish, $fileDir)
    {
        $dir = self::getPublishFilePath($idFacultySemester, $idPublish) . $fileDir;
        return self::disk()->delete($dir);
    }
    public static function getPublishFile($idFacultySemester, $idPublish, $path)
    {
        return self::disk()->get(self::getPublishFilePath($idFacultySemester, $idPublish, $path));
    }
    public static function saveAvatarUser($idUser, $type, UploadedFile $file, &$filePath = null)
    {
        $fileName = Carbon::now()->toDateString() . '-' . Str::random(6) . '-' . $file->getClientOriginalName();
        $filePath = self::getAvatarUserPath($idUser, $type);
        self::save($file, $filePath, $fileName);
        return [
            "full" => $filePath . $fileName,
            "file" => $fileName
        ];
    }
    public static function getAvatarUserPath($idUser, $type = "student", $path = '', $strict = false)
    {
        $folderPath = self::getTypeFolder(self::TYPES['AVATAR'], $strict) . $type . '/' . $idUser . '/';
        return $folderPath . $path;
    }
    public static function deleteAvatarUser($idUser, $type, $fileDir)
    {
        $dir = self::getAvatarUserPath($idUser, $type) . $fileDir;
        return self::disk()->delete($dir);
    }
    public static function getAvatarUser($idUser, $type, $path)
    {
        return self::disk()->get(self::getAvatarUserPath($idUser, $type, $path));
    }
    public static function saveArticleFileSubmission($idFacultySemester, $idArticle, UploadedFile $file, $prefix, &$filePath = null)
    {
        $fileName = $prefix . $file->getClientOriginalName();
        $filePath = self::getArticleFilePath($idFacultySemester, $idArticle);
        self::save($file, $filePath, $fileName);
        return $filePath . $fileName;
    }
    public static function getArticleFilePath($idFacultySemester, $idArticle, $path = '')
    {
        $folderPath = self::getTypeFolder(self::TYPES['ARTICLE']) . 'semester/' . $idFacultySemester . '/article/' . $idArticle . '/';
        return $folderPath . $path;
    }
    public static function deleteArticleFile($idFacultySemester, $idArticle, $fileDir)
    {
        $dir = self::getArticleFilePath($idFacultySemester, $idArticle) . $fileDir;
        return self::disk()->delete($dir);
    }
    public static function getArticleFile($id, $path)
    {
        return self::disk()->get(self::getArticleFilePath($id, $path));
    }
    public static function saveCommentCoordinator($idCoordinator, $idArticle, UploadedFile $file, &$filePath = null)
    {
        $fileName = Carbon::now()->toDateString() . '-' . Str::random(6) . '-' . $file->getClientOriginalName();
        $filePath = self::getCommentCoordinatorPath($idCoordinator, $idArticle);
        self::save($file, $filePath, $fileName);
        return $fileName;
    }
    public static function getCommentCoordinatorPath($idCoordinator, $idArticle, $path = '')
    {
        $folderPath = self::getTypeFolder(self::TYPES['COMMENT']) . 'article/' . $idArticle . '/coordinator/' . $idCoordinator . '/';
        return $folderPath . $path;
    }
    public static function getCommentCoordinator($idCoordinator, $id, $path)
    {
        return self::disk()->get(self::getCommentCoordinatorPath($idCoordinator, $id, $path));
    }
    public static function deleteCommentCoordinator($idCoordinator, $idArticle, $fileName)
    {
        $dir = self::getCommentCoordinatorPath($idCoordinator, $idArticle) . $fileName;
        return self::disk()->delete($dir);
    }
    public static function saveCommentStudent($idStudent, $idArticle, UploadedFile $file, &$filePath = null)
    {
        $fileName = Carbon::now()->toDateString() . '-' . Str::random(6) . '-' . $file->getClientOriginalName();
        $filePath = self::getCommentStudentPath($idStudent, $idArticle);
        self::save($file, $filePath, $fileName);
        return $fileName;
    }
    public static function getCommentStudentPath($idStudent, $idArticle, $fileName = '')
    {
        $folderPath = self::getTypeFolder(self::TYPES['COMMENT']) . 'article/' . $idArticle . '/student/' . $idStudent . '/';
        return $folderPath . $fileName;
    }
    public static function getCommentStudent($idStudent, $id, $path)
    {
        return self::disk()->get(self::getCommentStudentPath($idStudent, $id, $path));
    }
    public static function deleteCommentStudent($idStudent, $idArticle, $fileName)
    {
        $dir = self::getCommentStudentPath($idStudent, $idArticle) . $fileName;
        return self::disk()->delete($dir);
    }
    public static function saveProfile($idArticle, UploadedFile $file, &$filePath = null)
    {
        $fileName = Carbon::now()->toDateString() . '-' . Str::random(6) . '-' . $file->getClientOriginalName();
        $filePath = self::getProfilePath($idArticle);
        self::save($file, $filePath, $fileName);
        return $filePath . $fileName;
    }
    public static function getProfilePath($id, $path = '')
    {
        $folderPath = self::getTypeFolder(self::TYPES['PROFILE']) . $id . '/';
        return $folderPath . $path;
    }
    public static function getProfile($id, $path)
    {
        return self::disk()->get(self::getProfilePath($id, $path));
    }
    public static function copy($sourcePath, $targetPath)
    {
        return self::disk()->copy($sourcePath, $targetPath);
    }
    public static function move($sourcePath, $targetPath)
    {
        return self::disk()->move($sourcePath, $targetPath);
    }
    public static function get($path)
    {
        return self::disk()->get($path);
    }
    public static function locatePath($path)
    {
        return self::disk()->path($path);
    }
    public static function urlPath($path)
    {
        return self::disk()->url($path);
    }
    public static function mimeType($path)
    {
        return self::disk()->mimeType($path);
    }
}
