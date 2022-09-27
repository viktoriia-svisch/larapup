<?php
namespace App\Http\Requests;
use App\Rules\ArticleFileFilter;
use App\Rules\ArticleUploadCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UploadArticleRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(STUDENT_GUARD);
    }
    public function rules()
    {
        return [
            'faculty_semester_id' => ["required", new ArticleUploadCondition()],
            'semester_id' => "required|exists:semesters,id",
            "wordDocument" => ["array", new ArticleFileFilter($this->file("wordDocument"))]
        ];
    }
    public function messages()
    {
        return [
            "faculty_semester_id.required" => "Information about the faculty in the semester is missing.",
            "semester_id.*" => "Semester information is required and must be exist in the system."
        ];
    }
}
