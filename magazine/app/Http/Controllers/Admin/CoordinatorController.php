<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCoordinator;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\FacultySemesterStudent;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Input;
class CoordinatorController extends Controller
{
    public function coordinator(Request $request){
        $searchTerms = $request->get('search_student_input');
        $searchType = $request->get('type');
        $coordinatorList = Coordinator::with('faculty_semester_coordinator');
        if ($searchType != -1 && $searchType != null) {
            $coordinatorList->where('status', $request->get('type'));
        }
        if ($searchTerms != null){
            $coordinatorList->where(function ($query) use ($searchTerms) {
                $query->where('first_name', 'like', '%' . $searchTerms . '%')
                    ->orwhere('last_name', 'like', '%' . $searchTerms . '%');
            });
        }
        return view('admin.Coordinator.coordinator', ['coordinators' => $coordinatorList->paginate(PER_PAGE)]);
    }
    public function addToFaculty_index(){
        $faculty = Faculty::get();
        return view('admin.faculty.add-coordinator',
            ['faculties' => $faculty]);
    }
    public function fetch(Request $request){
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
    public function fetchCoor(Request $request){
        $semester = $request->get('semester');
        $faculty = $request->get('faculty');
        $output = '';
        $facultySemester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        if($facultySemester != null){
            $coors = Coordinator::whereNotHas("faculty_semester_coordinator",
                function (Builder $builder) use ($facultySemester) { $builder->where('faculty_semester_id', $facultySemester->id);});
            foreach ($coors as $coor){
                $output .='<div class="col-xl-12 align-items-center" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">'
                    .'<img  class="img-thumbnail col-xl-2" style="width: 53px"src="https:
                    .'<label class="col-xl-6">'. $coor->first_name . ' ' . $coor->last_name.'</label>'
                    .'<a href="'. route('admin.addToFaculty.addCoorToFaculty_post', ['faculty'=>$faculty,'semester'=>$semester, 'coordinator'=>$coor->id])
                    .'" class="col-xl-4 submit-coordinator">Add Coordinator</a>'
                    .'</div>';
            }
        }
        return $output;
    }
    public function createCoordinator_post(CreateCoordinator $request){
        $coordinator = new Coordinator($request->all([
            'email',
            'password',
            'first_name',
            'last_name',
            'gender',
            'dateOfBirth'
        ]));
        $coordinator->type = COORDINATOR_LEVEL['NORMAL'];
        if ($coordinator->save())
            return back()->with($this->responseBladeMessage(__('message.create_coordinator_success')));
        return back()->with($this->responseBladeMessage(__('message.create_coordinator_failed'), false));
    }
    public function create()
    {
        return view('admin.coordinator.create-coordinator');
    }
    public function addToFaculty($coordinator, $faculty, $semester){
        $faculty_semester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        $ad = new FacultySemesterCoordinator();
        $ad->faculty_semester_id = $faculty_semester->id;
        $ad->coordinator_id = $coordinator;
        if ($ad->save()){
            return redirect()->back()->with([
                'success' => true
            ]);
        }
        return redirect()->back()->with([
            'success' => false
        ]);
    }
    public function updateCoordinator($id)
    {
        $coordinator = Coordinator::find($id);
        return view('admin.Coordinator.update-coordinator', [
            'coordinator' => $coordinator
        ]);
    }
    public function updateCoordinatorPost(Request $request, $id)
    {
        $coordinator = Coordinator::find($id);
        if (!$coordinator) return redirect()->back();
        $coordinator->first_name = $request->get('first_name') ?? $coordinator->first_name;
        $coordinator->last_name = $request->get('last_name') ?? $coordinator->last_name;
        $coordinator->dateOfBirth = $request->get('dateOfBirth') ?? $coordinator->dateOfBirth;
        $coordinator->gender = $request->get('gender') ?? $coordinator->gender;
        $coordinator->status = $request->get('status') ?? $coordinator->status;
        if ($request->get('old_password')){
            if(Hash::check($request->get('old_password'),$coordinator->password)) {
                $coordinator->password =  $request->get('new_password');
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
}
