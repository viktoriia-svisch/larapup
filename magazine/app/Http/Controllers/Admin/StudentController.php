<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudent;
use App\Http\Requests\UpdateStudentByAdmin;
use App\Models\Student;
use Illuminate\Http\Request;
class StudentController extends Controller
{
    public function student(Request $request)
    {
        $searchTerms = $request->get('search_student_input');
        $searchType = $request->get('type');
        $studentList = Student::with('faculty_semester_student');
        if ($searchType != -1 && $searchType != null) {
            $studentList->where('status', $request->get('type'));
        }
        if ($searchTerms != null) {
            $studentList->where(function ($query) use ($searchTerms) {
                $query->where('first_name', 'like', '%' . $searchTerms . '%')
                    ->orwhere('last_name', 'like', '%' . $searchTerms . '%');
            });
        }
        return view('admin.student.student', [
            'availableStudent' => $studentList->paginate(PER_PAGE),
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
    public function updateStudentPost(UpdateStudentByAdmin $request, $id)
    {
        $student = Student::find($id);
        if (!$student) return redirect()->back();
        $student->first_name = $request->get('first_name') ?? $student->first_name;
        $student->last_name = $request->get('last_name') ?? $student->last_name;
        $student->dateOfBirth = $request->get('dateOfBirth') ?? $student->dateOfBirth;
        $student->gender = $request->get('gender') ?? $student->gender;
        $student->status = $request->get('status') ?? $student->status;
        $student->email = $request->get('email') ?? $student->email;
        if ($request->get('new_password')) {
            $student->password = $request->get('new_password');
        }
        if ($student->save()) {
            return back()->with($this->responseBladeMessage("Update successfully"));
        }
        return back()->with($this->responseBladeMessage("Update unsuccessfully", false));
    }
}
