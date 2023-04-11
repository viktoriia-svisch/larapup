<?php
namespace App\Rules;
use App\Helpers\UploadFileValidate;
use Exception;
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
            if (sizeof($this->request->get("old_image")) + sizeof($value) > 10) {
                $this->message = "Maximum 10 image per publishing only!";
                return false;
            }
            foreach ($value as $index => $file) {
                if (gettype($file) !== "UploadedFile") {
                    $this->message = "Not a image file";
                    return false;
                }
                if (!UploadFileValidate::checkIfImage($file->getClientOriginalExtension())) {
                    $this->message = "Not supported image extension";
                    return false;
                }
                if ($file->getSize() > FILE_MAXSIZE) {
                    $this->message = "File larger 10MB";
                    return false;
                }
                try {
                    if (!$this->request->get("imageDescription")[$index] || !$this->request->get("description")[$index]) {
                        $this->message = "Image without provided description is not allowed at section " . ($index + 1);
                        return false;
                    }
                } catch (Exception $e) {
                    $this->message = "Image without provided description is not allowed at section " . ($index + 1);
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
