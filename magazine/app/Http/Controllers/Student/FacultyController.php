<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $facultiesPassed = Faculty::with(['faculty_semester.faculty_semester_student.student'])
            ->whereHas('faculty_semester.faculty_semester_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('faculty_semester.semester', function ($query) {
                $query->whereDate('end_date', "<=", Carbon::now()->toDateTimeString());
            })
            ->paginate(1);
        $facultiesFuture = Faculty::with(['faculty_semester.faculty_semester_student.student'])
            ->whereHas('faculty_semester.faculty_semester_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('faculty_semester.semester', function ($query) {
                $query->whereDate('start_date', ">", Carbon::now()->toDateTimeString());
            })
            ->paginate(1);
        $currentFaculty = Faculty::with(['faculty_semester.faculty_semester_student.student'])
            ->whereHas('faculty_semester.faculty_semester_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('faculty_semester.semester', function ($query) {
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
        if ($faculty)
            return view('student.faculty.faculty-detail', [
                'faculty' => $faculty
            ]);
        else
            redirect()->route('student.faculty');
    }
}
