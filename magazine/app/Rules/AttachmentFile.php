<?php
namespace App\Rules;
use App\Helpers\UploadFileValidate;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
class AttachmentFile implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        if ($value) {
            $validEXT = UploadFileValidate::checkExtension($value->getClientOriginalExtension());
            $validMIME = UploadFileValidate::checkMime($value->getClientMimeType());
            $sizeValid = $value->getSize() < FILE_MAXSIZE;
            $validatedImage = $validEXT && $validMIME && $sizeValid;
            return $validatedImage;
        }
        return true;
    }
    public function message()
    {
        return 'Unsupported file. Please upload supported file (.docx, .jpeg, .zip)';
    }
}
