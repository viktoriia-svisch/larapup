<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class StudentController extends Controller
{
    public function student(Request $request)
    {
        $studentList = Student::with('faculty_semester_student')
            ->whereDoesntHave('faculty_semester_student.faculty_semester', function ($q) {
                $q->whereHas('semester', function ($q) {
                    $q->where('end_date', '>=', Carbon::now()->toDateString());
                });
            })
            ->get();
        return view('admin.student.student', [
            'availableStudent' => $studentList
        ]);
    }
    public function createStudent()
    {
        return view('admin.student.create-student');
    }
    public function createStudent_post(Request $request)
    {
        $student = new Student($request->all([
            'email',
            'password',
            'first_name',
            'last_name',
            'gender',
            'dateOfBirth'
        ]));
        if ($student->save())
            return redirect()->back()->with([
                'success' => true
            ]);
        return redirect()->back()->with([
            'success' => false
        ]);
    }
}
