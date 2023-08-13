<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCoordinatorAccount;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class CoordinatorController extends Controller
{
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
            ->whereHas('semester', function (Builder $query){
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
        $coordinator = Coordinator::with("faculty_semester_coordinator")->find($id);
        return view('coordinator.manage.coordinator-detail', [
            'coordinator' => $coordinator]);
    }
    public function updateCoordinatorPost(UpdateCoordinatorAccount $request, $id)
    {
        $coordinator = Coordinator::with("faculty_semester_coordinator")->find($id);
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
        $semester = Semester::with("faculty_semester")->find($semester);
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
        $FacultySemester = FacultySemester::with("faculty")->find($facultysemester);
        $semester = Semester::with("faculty_semester")->where('id', '=', $FacultySemester->semester_id)->get();
        $faculty = Faculty::with("faculty_semester")->where('id', '=', $FacultySemester->faculty_id)->get();
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
        $student = Student::with("faculty_semester_student")->find($student);
        $FacultySemester = Faculty::with("faculty_semester")->find($FacultySemester);
        $FacuSemeStudent = new FacultySemesterStudent;
        $FacuSemeStudent->faculty_semester_id = $FacultySemester->id;
        $FacuSemeStudent->student_id = $student->id;
        $HasFaculty = FacultySemesterStudent::with("faculty_semester")->where('student_id', '=', $student->id)->first();
        if (!empty($HasFaculty)) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
        }
        if ($FacuSemeStudent->save()) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
        }
    }
    public function addToFaculty_index()
    {
        $faculty = Faculty::with("faculty_semester")->get();
        return view('coordinator.Faculty.add-coordinator',
            ['faculties' => $faculty]);
    }
    public function fetch(Request $request)
    {
        $value = $request->get('value');
        $output = '<option value="0">Select a semester</option>';
        $faculty_semester = FacultySemester::with('semester')->where('faculty_id', '=', $value)->get();
        foreach ($faculty_semester as $row) {
            $data = Semester::with("faculty_semester")
                ->where('id', "=", $row->semester_id)
                ->where('end_date', '>=', Carbon::now())
                ->first();
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
        $availableCoor = '';
        $unavailableCoor = '';
        $facultySemester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        if ($facultySemester != null) {
            $coors = Coordinator::with("faculty_semester_coordinator")
                ->whereDoesntHave("faculty_semester_coordinator",
                function (Builder $builder) use ($facultySemester) {
                    $builder->where('faculty_semester_id', $facultySemester->id);
                })->get();
            $unavailableCoors = Coordinator::whereHas('faculty_semester_coordinator', function (Builder $builder) use ($facultySemester) {
                $builder->where('faculty_semester_id', '=', $facultySemester->id);
            })->get();
            foreach ($coors as $coor) {
                $availableCoor .= '<div class="card mb-3 pt-4 pb-4" style="">
                  <div class="row no-gutters">
                    <div class="col-12 col-md-auto d-flex align-items-center pl-4" >
                      <img src="https:
                    </div>
                    <div class="col-12 col-md d-flex align-items-center">
                      <div class="card-body p-0 pl-4">
                        <h5 class="card-title">' . $coor->first_name . ' ' . $coor->last_name . '</h5>
                        <a href="' . route('coordinator.faculty.addToFaculty.addCoorToFaculty_post', ['faculty' => $faculty, 'semester' => $semester, 'coordinator' => $coor->id]) . '" class="col-xl-4 submit-coordinator">Add</a>
                      </div>
                    </div>
                  </div>
                </div>';
            }
            foreach ($unavailableCoors as $coor) {
                $unavailableCoor .= '<div class="card mb-3 pt-4 pb-4" style="">
                  <div class="row no-gutters">
                    <div class="col-12 col-md-auto d-flex align-items-center pl-4">
                      <img src="https:
                    </div>
                    <div class="col-12 col-md d-flex align-items-center">
                      <div class="card-body p-0 pl-4">
                        <h5 class="card-title">' . $coor->first_name . ' ' . $coor->last_name . '</h5>
                        <a href="' . route('coordinator.faculty.addToFaculty.removeCoorFromFaculty_post', ['faculty' => $faculty, 'semester' => $semester, 'coordinator' => $coor->id]) . '" class="col-xl-4 submit-coordinator">Remove</a>
                      </div>
                    </div>
                  </div>
                </div>';
            }
            $output = ['availableCoor' => $availableCoor, 'unavailableCoor' => $unavailableCoor];
        }
        return $output;
    }
    public function addToFaculty($coordinator, $faculty, $semester)
    {
        $faculty_semester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        $coor = Coordinator::where('id', '=', $coordinator);
        $ad = new FacultySemesterCoordinator();
        $ad->faculty_semester_id = $faculty_semester->id;
        $ad->coordinator_id = $coordinator;
        if (!$faculty_semester || !$coor) {
            return redirect()->back()->with($this->responseBladeMessage("Add coordinator fail! - Coordinator does not exist!", false));
        }
        if ($ad->save()) {
            return redirect()->back()->with($this->responseBladeMessage("Add coordinator success!", true));
        }
        return redirect()->back()->with($this->responseBladeMessage("Add coordinator fail!", false));
    }
    public function removeToFaculty($coordinator, $faculty, $semester)
    {
        $faculty_semester_coordinator = FacultySemesterCoordinator::where('coordinator_id', '=', $coordinator)
            ->whereHas("faculty_semester", function (Builder $builder) use ($faculty, $semester) {
                $builder->where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester);
            })
            ->first();
        if (!$faculty_semester_coordinator) {
            return redirect()->back()->with($this->responseBladeMessage("Remove coordinator fail! - Coordinator does not exist!", false));
        }
        if ($faculty_semester_coordinator->delete()) {
            return redirect()->back()->with($this->responseBladeMessage("Remove coordinator success!", true));
        }
        return redirect()->back()->with($this->responseBladeMessage("Remove coordinator fail!", false));
    }
}
