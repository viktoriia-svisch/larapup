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
            "description" => ["required", "min:3", 'max:1500'],
            "old_image" => ["array", 'max:10'],
            "image" => ['array', "max:10", new PublishImageRule($this)]
        ];
    }
}
