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
        $coordinators = Coordinator::where('first_name', 'LIKE', '%' . $request->get('search_coordinator_input') . '%')
            ->orWhere('last_name', 'like', '%' . $request->get('search_coordinator_input') . '%')
            ->paginate(PER_PAGE);
        return view('admin.Coordinator.coordinator', ['coordinators' => $coordinators]);
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
            $notAvailableCoor = FacultySemesterCoordinator::where('faculty_semester_id', '=', $facultySemester->id)->get('coordinator_id');
            $coors = Coordinator::whereNotIn('id', $notAvailableCoor)->get();
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
}
