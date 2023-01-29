<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class CoordinatorController extends Controller
{
    public function index()
    {
        $coordinators = Coordinator::paginate(PER_PAGE);
        return CoordinatorResource::collection($coordinators);
    }
    public function dashboard()
    {
        return view('coordinator.dashboard');
    }
    public function search($request)
    {
        $search = Coordinator::where('first_name', 'LIKE', '%' . $request . '%')
            ->orWhere('last_name', 'like', '%' . $request . '%')
            ->get();
        return response()->json($search);
    }
    public function show($id)
    {
        $coordinator = Coordinator::find($id);
        return new CoordinatorResource($coordinator);
    }
    public function CoordinatorSemester()
    {
        $facultySemesterCoordinator = FacultySemesterCoordinator::with("faculty_semester")
        ->where('coordinator_id', Auth::guard(COORDINATOR_GUARD)->user()->id)
        ->whereHas("faculty_semester.semester")
        ->first();
        return view('coordinator.Semester.semester', [
            'facSemesterCoordinator' => $facultySemesterCoordinator]);
    }
    public function chooseSemesterFaculty($semester)
    {
        $semester = Semester::find($semester);
        $faculty = FacultySemesterCoordinator::with("faculty_semester")
            ->where('coordinator_id', Auth::guard(COORDINATOR_GUARD)->user()->id)
            ->whereHas("faculty_semester.faculty")->get();
        $StudentList = Student::all();
        $FacultySemester = DB::table('faculty_semesters')
            ->join('faculties', 'faculty_semesters.faculty_id', '=', 'faculties.id')
            ->join('faculty_semester_coordinators','faculty_semesters.id', '=', 'faculty_semester_coordinators.faculty_semester_id')
            ->select('faculties.name','faculty_semesters.id')
            ->where('faculty_semesters.semester_id','=',$semester->id)
            ->where('faculty_semester_coordinators.coordinator_id','=',Auth::guard(COORDINATOR_GUARD)->user()->id)
            ->get();
        return view('coordinator.Semester.choose-semester-faculty')
            ->with('semester',$semester)
            ->with('faculties',$faculty)
            ->with('StudentList',$StudentList)
            ->with('FacultySemester',$FacultySemester);
    }
    public function addStudentFaculty($facultysemester){
        $FacultySemester = FacultySemester::find($facultysemester);
        $semester = Semester::where('id','=',$FacultySemester->semester_id)->get();
        $faculty = Faculty::where('id','=',$FacultySemester->faculty_id)->get();
        $StudentList = Student::all();
        $AvailableStudent = DB::table('faculty_semester_students')
            ->join('students', 'faculty_semester_students.student_id', '=', 'students.id')
            ->select('students.first_name','students.last_name')
            ->where('faculty_semester_students.faculty_semester_id','=',$FacultySemester->id)
            ->get();
        return view('admin.faculty.add-student')
            ->with('semester',$semester)
            ->with('faculty',$faculty)
            ->with('StudentList',$StudentList)
            ->with('AvailableStudent',$AvailableStudent)
            ->with('FacultySemester',$FacultySemester);
    }
    public function addStudentFaculty_post($FacultySemester,$student){
        $student = Student::find($student);
        $FacuSemeStudent = new FacultySemesterStudent;
        $FacuSemeStudent->faculty_semester_id = $FacultySemester;
        $FacuSemeStudent->student_id = $student->id;
        $HasFaculty = FacultySemesterStudent::where('student_id','=',$student->id)->first();
        if(!empty($HasFaculty))
        {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
        }
        if ($FacuSemeStudent->save())
        {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
        }
    }
}
