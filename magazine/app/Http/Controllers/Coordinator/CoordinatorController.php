<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCoordinatorAccount;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
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
    public function Coordinator()
    {
        $currentSemester = Semester::with('faculty_semester')
            ->where('start_date', '<=', Carbon::now()->toDateTimeString())
            ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function ($q) {
                $q->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->where('end_date', '>', Carbon::now()->toDateTimeString())
            ->first();
        $currentFaculty = Faculty::with('faculty_semester.semester')
            ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function ($q) {
                $q->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->whereHas('faculty_semester.semester', function ($q) {
                $q->where('start_date', '<=', Carbon::now()->toDateTimeString())
                    ->where('end_date', '>', Carbon::now()->toDateTimeString());
            })
            ->first();
        $currentActiveData = FacultySemester::with(['faculty', 'semester'])
            ->whereHas('faculty_semester_coordinator.coordinator', function ($query) {
                $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($query){
                $query->where('start_date', '<=', Carbon::now()->toDateTimeString())
                    ->where('end_date', '>', Carbon::now()->toDateTimeString());
            })
            ->orderBy('second_deadline')->first();
        return view('coordinator.manage-coordinator',[
            'activeData' => $currentActiveData,
            'activeSemester' => null,
            'activeFaculty' => null
        ]);
    }
    public function updateCoordinator($id){
        $coordinator = Coordinator::find($id);
        return view('coordinator.manage.coordinator-detail',[
            'coordinator' => $coordinator]);
    }
    public function updateCoordinatorPost(UpdateCoordinatorAccount $request, $id)
    {
        $coordinator = Coordinator::find($id);
        if (!$coordinator) return redirect()->back()->withInput();
        $coordinator->first_name = $request->get('first_name') ?? $coordinator->first_name;
        $coordinator->last_name = $request->get('last_name') ?? $coordinator->last_name;
        $coordinator->dateOfBirth = $request->get('dateOfBirth') ?? $coordinator->dateOfBirth;
        $coordinator->gender = $request->get('gender') ?? $coordinator->gender;
        if ($request->get('old_password')) {
            if (Hash::check($request->get('old_password'), $coordinator->password)) {
                $coordinator->password = $request->get('new_password');
            } else {
                return back()->with([
                    'updateStatus' => false
                ]);
            }
        }
        if ($coordinator->save()) {
            return back()->with([
                'updateStatus' => true
            ]);
        }
        return back()->with([
            'updateStatus' => false
        ]);
    }
    public function dashboard(){
        return view('coordinator.dashboard');
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
