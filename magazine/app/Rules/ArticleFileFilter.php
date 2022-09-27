<?php
namespace App\Rules;
use App\Helpers\UploadFileValidate;
use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class ArticleFileFilter implements Rule
{
    private $arrFiles;
    private $errMessage;
    public function __construct($files)
    {
        $this->arrFiles = $files;
    }
    public function passes($attribute, $value)
    {
        if (count($this->arrFiles) > 3) {
            $this->errMessage = "You can only upload 3 files for an article. Please try again.";
            return false;
        }
        $validated = false;
        foreach ($this->arrFiles as $file) {
            $validEXT = UploadFileValidate::checkExtension($file->getClientOriginalExtension());
            $validMIME = UploadFileValidate::checkMime($file->getClientMimeType());
            $sizeValid = $file->getSize() < FILE_MAXSIZE;
            $validated = $validEXT && $validMIME && $sizeValid;
            if (!$validated) break;
        }
        $this->errMessage = "(One of) the file(s) was not word document or image, or the size is to big (max 10mb per file)";
        return $validated;
    }
    public function message()
    {
        return $this->errMessage;
    }
}
