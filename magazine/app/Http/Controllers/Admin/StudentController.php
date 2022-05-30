<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudent;
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
    public function createStudent_post(CreateStudent $request)
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
            return back()->with($this->responseBladeMessage(__('message.create_student_success')));
        return back()->with($this->responseBladeMessage(__('message.create_student_failed'), false));
    }
    public function updateStudent($id)
    {
        $student = Student::find($id);
        return view('admin.student.update-student', [
            'student' => $student
        ]);
    }
    public function updateStudentPost(Request $request, $id)
    {
        $student = Student::find($id);
        if (!$student) return redirect()->back();
        $student->first_name = $request->get('first_name') ?? $student->first_name;
        $student->last_name = $request->get('last_name') ?? $student->last_name;
        $student->dateOfBirth = $request->get('dateOfBirth') ?? $student->dateOfBirth;
        $student->gender = $request->get('gender') ?? $student->gender;
        dd($request, $student);
        if ($request->get('new_password')) {
            $student->password = $request->get('new_password');
        }
        if ($student->save()) {
            return back()->with([
                'updateStatus' => true
            ]);
        }
        return back()->with([
            'updateStatus' => false
        ]);
    }
}
