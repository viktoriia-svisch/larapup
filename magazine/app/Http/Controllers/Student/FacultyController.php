<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class FacultyController extends Controller
{
    public function faculty()
    {
        $facultiesPassed = Faculty::with(['faculty_student.student', 'semester'])
            ->whereHas('faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($query) {
                $query->whereDate('end_date', "<=", Carbon::now()->toDateTimeString());
            })
            ->get();
        $facultiesFuture = Faculty::with(['faculty_student.student', 'semester'])
            ->whereHas('faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($query) {
                $query->whereDate('start_date', ">", Carbon::now()->toDateTimeString());
            })
            ->get();
        $currentFaculty = Faculty::with(['faculty_student.student', 'semester'])
            ->whereHas('faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($query) {
                $query->whereDate('start_date', "<", Carbon::now()->toDateTimeString())
                    ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());
            })
            ->first();
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
