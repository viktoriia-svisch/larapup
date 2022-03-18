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
        $searchTerms = $request->get('search_student_input');
        $searchType = $request->get('type');
        $studentList = Student::with('faculty_student.faculty');
        if ($searchType != -1 && $searchType != null) {
            $studentList->where('status', $request->get('type'));
        }
        if ($searchTerms != null){
            $studentList->where(function ($query) use ($searchTerms) {
                    $query->where('first_name', 'like', '%' . $searchTerms . '%')
                        ->orwhere('last_name', 'like', '%' . $searchTerms . '%');
                });
        }
        $studentList->whereDoesntHave('faculty_student.faculty', function ($q) {
            $q->whereHas('semester', function ($q) {
                $q->where('end_date', '>=', Carbon::now()->toDateString());
            });
        });
        return view('admin.student.student', [
            'availableStudent' => $studentList->paginate(PER_PAGE),
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
