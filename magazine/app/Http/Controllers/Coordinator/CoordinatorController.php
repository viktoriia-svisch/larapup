<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCoordinatorAccount;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Http\Resources\Faculty as FacultyResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\FacultySemester;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
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
<<<<<<< HEAD
    public function statistics(){
        $faculties = Faculty::all();
        return view('coordinator.statistics', compact('faculties'));
    }
    public function facultySemester(){
        $faculty_id = Input::get('faculty_id');
        $Fsemesters = FacultySemester::where('faculty_id', '=', $faculty_id)->get();
        $semesters = DB::table('faculty_semesters')
            ->join('semesters', 'faculty_semesters.semester_id', '=', 'semesters.id')
            ->select('faculty_semesters.id', 'semesters.name')
            ->where('faculty_semesters.faculty_id','=',$faculty_id)
            ->get();
        return response()->json($semesters);
    }
    public function dashboard()
    {
=======
    public function dashboard(){
>>>>>>> parent of d8cfcd3... Add Statistics UI
        return view('coordinator.dashboard');
    }
    public function show($id)
    {
        $coordinator = Coordinator::find($id);
        return new CoordinatorResource($coordinator);
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
