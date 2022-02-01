<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class FacultyController extends Controller
{
    public function faculty()
    {
        $facultyBased = Faculty::with(['faculty_student.student', 'semester'])
            ->whereHas('faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            });
        $facultiesPassed = $facultyBased->whereHas('semester', function ($query) {
                $query->where('end_date', "<=", Carbon::now()->toDateTimeString());
            })->get();
        $facultiesFuture = $facultyBased->whereHas('semester', function ($query) {
                $query->where('start_date', ">", Carbon::now()->toDateTimeString());
            })->get();
        $currentFaculty = $facultyBased->whereHas('semester', function ($query) {
            $query->where('start_date', "<=", Carbon::now()->toDateTimeString())
                ->where('end_date', ">=", Carbon::now()->toDateTimeString());
        })->first();
        dd($facultyBased, $currentFaculty, Carbon::now()->toDateTimeString(), Auth::guard(STUDENT_GUARD)->user()->id);
        return view('student.faculty.faculties', [
            'passedFaculties' => $facultiesPassed,
            'futureFaculties' => $facultiesFuture,
            'currentFaculty' => $currentFaculty
        ]);
    }
    public function facultyDetail($id)
    {
        $faculty = Faculty::with(['faculty_student.student', 'semester'])
            ->where('id', $id)
            ->whereHas('faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })->first();
        return view('student.faculty.faculty-detail', [
            'faculty' => $faculty
        ]);
    }
}
