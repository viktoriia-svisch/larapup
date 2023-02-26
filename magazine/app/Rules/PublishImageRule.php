<?php
namespace App\Rules;
use App\Helpers\UploadFileValidate;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class PublishImageRule implements Rule
{
    private $request;
    private $message;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        if (is_array($value) && sizeof($value) > 0) {
            if (
                ($this->request->get("old_image") &&
                    sizeof($this->request->get("old_image")) + sizeof($value) > 10) ||
                sizeof($value) > 10) {
                $this->message = "Maximum 10 image per publishing only!";
                return false;
            }
            foreach ($value as $index => $file) {
                if (!UploadFileValidate::checkIfImage($file->getClientOriginalExtension())) {
                    $this->message = "Not supported image extension";
                    return false;
                }
                if ($file->getSize() > FILE_MAXSIZE / 2) {
                    $this->message = "File larger 5MB spotted!";
                    return false;
                }
                if (!UploadFileValidate::checkMime($file->getClientMimeType())) {
                    $this->message = "Unsupported file MIME.";
                    return false;
                }
            }
            return true;
        } else {
            return true;
        }
    }
    public function message()
    {
        return $this->message;
    }
}
