<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCoordinatorAccount;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\FacultySemesterStudent;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class CoordinatorController extends Controller
{
    public function index()
    {
        dd('1');
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
            ->whereHas('semester', function ($query) {
                $query->where('start_date', '<=', Carbon::now()->toDateTimeString())
                    ->where('end_date', '>', Carbon::now()->toDateTimeString());
            })
            ->orderBy('second_deadline')->first();
        return view('coordinator.manage-coordinator', [
            'activeData' => $currentActiveData,
            'activeSemester' => null,
            'activeFaculty' => null
        ]);
    }
    public function updateCoordinator($id)
    {
        $coordinator = Coordinator::find($id);
        return view('coordinator.manage.coordinator-detail', [
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
    public function dashboard()
    {
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
            ->join('faculty_semester_coordinators', 'faculty_semesters.id', '=', 'faculty_semester_coordinators.faculty_semester_id')
            ->select('faculties.name', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $semester->id)
            ->where('faculty_semester_coordinators.coordinator_id', '=', Auth::guard(COORDINATOR_GUARD)->user()->id)
            ->get();
        return view('coordinator.Semester.choose-semester-faculty')
            ->with('semester', $semester)
            ->with('faculties', $faculty)
            ->with('StudentList', $StudentList)
            ->with('FacultySemester', $FacultySemester);
    }
    public function addStudentFaculty($facultysemester)
    {
        $FacultySemester = FacultySemester::find($facultysemester);
        $semester = Semester::where('id', '=', $FacultySemester->semester_id)->get();
        $faculty = Faculty::where('id', '=', $FacultySemester->faculty_id)->get();
        $StudentList = Student::all();
        $AvailableStudent = DB::table('faculty_semester_students')
            ->join('students', 'faculty_semester_students.student_id', '=', 'students.id')
            ->select('students.first_name', 'students.last_name')
            ->where('faculty_semester_students.faculty_semester_id', '=', $FacultySemester->id)
            ->get();
        return view('coordinator.Semester.add-student')
            ->with('semester', $semester)
            ->with('faculty', $faculty)
            ->with('StudentList', $StudentList)
            ->with('AvailableStudent', $AvailableStudent)
            ->with('FacultySemester', $FacultySemester);
    }
    public function addStudentFaculty_post($FacultySemester, $student)
    {
        $student = Student::find($student);
        $FacultySemester = Faculty::find($FacultySemester);
        $FacuSemeStudent = new FacultySemesterStudent;
        $FacuSemeStudent->faculty_semester_id = $FacultySemester->id;
        $FacuSemeStudent->student_id = $student->id;
        $HasFaculty = FacultySemesterStudent::where('student_id', '=', $student->id)->first();
        if (!empty($HasFaculty)) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
        }
        if ($FacuSemeStudent->save()) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
        }
    }
    public function addToFaculty_index()
    {
        $faculty = Faculty::get();
        return view('coordinator.Faculty.add-coordinator',
            ['faculties' => $faculty]);
    }
    public function fetch(Request $request)
    {
        $value = $request->get('value');
        $output = '<option value="0">Select a semester</option>';
        $faculty_semester = FacultySemester::with('semester')->where('faculty_id', '=', $value)->get();
        foreach ($faculty_semester as $row) {
            $data = Semester::where('id', "=", $row->semester_id)->where('end_date', '>=', Carbon::now())->first();
            if ($data != null) {
                $output .= '<option value="' . $data->id . '">' . $data->name . '</option>';
            }
        }
        return $output;
    }
    public function fetchCoor(Request $request)
    {
        $semester = $request->get('semester');
        $faculty = $request->get('faculty');
        $output = '';
        $facultySemester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        if ($facultySemester != null) {
            $coors = Coordinator::whereDoesntHave("faculty_semester_coordinator",
                function (Builder $builder) use ($facultySemester) {
                    $builder->where('faculty_semester_id', $facultySemester->id);
                })->get();
            foreach ($coors as $coor) {
                $output .= '<div class="col-xl-12 align-items-center" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">'
                    . '<img  class="img-thumbnail col-xl-2" style="width: 53px"src="https:
                    . '<label class="col-xl-6">' . $coor->first_name . ' ' . $coor->last_name . '</label>'
                    . '<a href="' . route('coordinator.addToFaculty.addCoorToFaculty_post', ['faculty' => $faculty, 'semester' => $semester, 'coordinator' => $coor->id])
                    . '" class="col-xl-4 submit-coordinator">Add Coordinator</a>'
                    . '</div>';
            }
        }
        return $output;
    }
    public function addToFaculty($coordinator, $faculty, $semester)
    {
        $faculty_semester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        $ad = new FacultySemesterCoordinator();
        $ad->faculty_semester_id = $faculty_semester->id;
        $ad->coordinator_id = $coordinator;
        if ($ad->save()) {
            return redirect()->back()->with([
                'success' => true
            ]);
        }
        return redirect()->back()->with([
            'success' => false
        ]);
    }
}
