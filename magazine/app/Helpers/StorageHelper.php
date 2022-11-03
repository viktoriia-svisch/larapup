<?php
namespace App\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
class StorageHelper
{
    const TYPES = [
        'ARTICLE' => 1,
        'COMMENT' => 2,
        'PROFILE' => 3,
        'PUBLISH' => 4,
    ];
    const TYPE_NAMES = [
        1 => 'articles',
        2 => 'comments',
        3 => 'profiles',
        4 => 'publishes',
    ];
    public static function saveArticle($idArticle, $file, &$filePath = null)
    {
        $fileName = $file->getClientOriginalName();
        $filePath = self::getArticlePath($idArticle);
        self::save($file, $filePath, $fileName);
        return $filePath . $fileName;
    }
    public static function getArticlePath($id, $path = '')
    {
        $folderPath = self::getTypeFolder(self::TYPES['ARTICLE']) . $id . '/';
        return $folderPath . $path;
    }
    public static function getArticle($id, $path)
    {
        return self::disk()->get(self::getArticlePath($id, $path));
    }
    public static function saveComment($idArticle, $file, &$filePath = null)
    {
        $fileName = $file->getClientOriginalName();
        $filePath = self::getCommentPath($idArticle);
        self::save($file, $filePath, $fileName);
        return $filePath . $fileName;
    }
    public static function getCommentPath($id, $path = '')
    {
        $folderPath = self::getTypeFolder(self::TYPES['COMMENT']) . $id . '/';
        return $folderPath . $path;
    }
    public static function getComment($id, $path)
    {
        return self::disk()->get(self::getCommentPath($id, $path));
    }
    public static function saveProfile($idArticle, $file, &$filePath = null)
    {
        $fileName = $file->getClientOriginalName();
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
    private static function disk()
    {
        return Storage::disk('local');
    }
    private static function getTypeFolder($type)
    {
        if (!isset(self::TYPE_NAMES[$type]))
            throw new \Exception('type is undefined');
        return 'data/local/' . self::TYPE_NAMES[$type] . '/';
    }
    public static function save($file, $path, $fileName = '')
    {
        if (!$fileName) {
            $fileName = $file->getClientOriginalName();
        }
        self::disk()->putFileAs($path, $file, $fileName);
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
