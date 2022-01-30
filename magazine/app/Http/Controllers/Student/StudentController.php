<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudent;
use App\Http\Resources\Student as StudentResource;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class StudentController extends Controller
{
    public function index()
    {
        $students = Student::paginate(PER_PAGE);
        dd($students);
    }
    public function article()
    {
        return view('shared.article');
    }
    public function dashboard()
    {
        $currentSemester = Semester::with('faculty')
            ->where('start_date', '<=', Carbon::now()->toDateTimeString())
            ->whereHas('faculty.faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->where('end_date', '>', Carbon::now()->toDateTimeString())
            ->first();
        $currentFaculty = Faculty::with('semester')
            ->whereHas('faculty_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($q) {
                $q->where('start_date', '<=', Carbon::now()->toDateTimeString())
                    ->where('end_date', '>', Carbon::now()->toDateTimeString());
            })
            ->first();
        return view('student.dashboard',[
            'activeSemester' => $currentSemester,
            'activeFaculty' => $currentFaculty
        ]);
    }
    public function store(CreateStudent $request)
    {
        $std = new Student();
        $std->email = $request->get('email');
        $std->password = $request->get('password');
        $std->firstname = $request->get('first_name');
        $std->lastname = $request->get('last_name');
        $std->status = 1;
        if ($std->save())
            return $this->responseMessage(
                'New student created successfully',
                false,
                'success',
                $std
            );
        return $this->responseMessage('Create unsuccessfully', true);
    }
    public function search($request)
    {
        $search = Student::where('first_name', 'LIKE', '%' . $request . '%')
            ->orWhere('last_name', 'like', '%' . $request . '%')
            ->get();
        return response()->json($search);
    }
    public function searchAll(Request $request)
    {
        $data = $request->get('data');
        $search = Student::where('first_name', 'like', "%{$data}%")
            ->orWhere('last_name', 'like', "%{$data}%")
            ->get();
        return response()->json($search);
    }
    public function show($id)
    {
        $student = Student::find($id);
        dd($student);
    }
}
