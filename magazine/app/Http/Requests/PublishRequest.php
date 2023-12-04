<?php
namespace App\Http\Requests;
use App\Rules\PublishImageRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class PublishRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(COORDINATOR_GUARD)->check();
    }
    public function rules()
    {
        return [
            "title" => "required|min:3|max:170",
            "description" => ["required", "min:3", 'max:1500'],
            "grade" => 'required|integer|between:1,10',
            "old_image" => ["array", 'max:10'],
            "image" => ['array', "max:10", new PublishImageRule($this)]
        ];
    }
    public function attributes()
    {
        return [
            "old_image" => "Existed image",
            "image" => "New Image",
        ];
    }
}
