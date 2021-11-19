<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        return SemesterResource::collection($sem);
    }
    public function store(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $ad->end_date = $request->get('end_date');
        if ($ad->save())
            return $this->responseMessage(
                'New semester created successfully',
                false,
                'success',
                $ad
            );
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
            ->where('status', STUDENT_STATUS['ONGOING'])
            ->get();
        return view('admin.student.student', [
            'availableStudent' => $studentList
        ]);
    }
}
