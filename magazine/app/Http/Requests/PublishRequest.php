<?php
namespace App\Http\Requests;
use App\Rules\PublishDescriptionRule;
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
            "description" => ["required", "array", 'max:5', new PublishDescriptionRule],
            "imageDescription" => ["array", 'max:5'],
            "image" => ['array', "max:5", new PublishImageRule($this)]
        ];
    }
}
