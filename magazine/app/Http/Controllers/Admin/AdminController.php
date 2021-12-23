<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Resources\Semester as SemesterResource;
use Illuminate\Support\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        dd($sem);
    }
    public function store(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $ad->end_date = $request->get('end_date');
        $validated = $request->validated();
        $OneYearLimit = Carbon::parse($ad->start_date)->addYear();
         if ($ad->end_date > $OneYearLimit){
              return $this->responseMessage("The end date must not be more than 1 year from start date", true);
         }
         if ($ad->save())
        {
            return $this->responseMessage(
                'New semester created successfully',
                false,
                'success',
                $ad
            );
        }
        return $this->responseMessage('Create unsuccessfully', true);
    }
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    public function semester()
    {
        $semesters = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator']);
        return view('admin.Semester.semester', [
            'listSemester' => $semesters
        ]);
    }
    public function createSemester()
    {
        return view('admin.Semester.create-semester');
    }
    public function student(Request $request)
    {
        $studentList = Student::with('faculty_student.faculty')
            ->whereDoesntHave('faculty_student.faculty', function ($q) {
                $q->whereHas('semester', function ($q) {
                    $q->where('end_date', '>=', Carbon::now()->toDateString(DATE_FORMAT));
                });
            })
            ->get();
        return view('admin.student.student', [
            'availableStudent' => $studentList
        ]);
    }
    public function createStudent(){
        return view('admin.student.create-student');
    }
    public function createStudent_post(Request $request){
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
