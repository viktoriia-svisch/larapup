<?php
namespace App\Http\Requests;
use App\Rules\AttachmentFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class CommentRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }
    public function rules()
    {
        return [
            "content" => "required|min:1|max:150",
            "attachment" => ["file", "nullable", new AttachmentFile()]
        ];
    }
}
