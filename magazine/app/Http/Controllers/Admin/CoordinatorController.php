<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class CoordinatorController extends Controller
{
    public function coordinator(Request $request){
        $coordinators = Coordinator::where('first_name', 'LIKE', '%' . $request->get('search') . '%')
            ->orWhere('last_name', 'like', '%' . $request->get('search') . '%')
            ->paginate(PER_PAGE);
        return view('admin.Coordinator.coordinator', ['coordinators' => $coordinators]);
    }
    public function addToFaculty_index(){
        $current = Carbon::now();
        $semester = Semester::whereDate('end_date', '>=', $current)->get();
        $coordinator = Coordinator::get();
        return view('admin.Coordinator.addToFaculty',
            ['semester' => $semester,
                'coordinator' => $coordinator]);
    }
    public function fetch(Request $request){
        $value = $request->get('value');
        $faculty_semester = FacultySemester::where('semester_id', '=', $value)->get(['faculty_id']);
        $data = Faculty::whereNotIn('faculty_id', $faculty_semester)->get();
        $output = '<option value="">Select faculty</option>';
        foreach ($data as $row){
            $output .= '<option value="'. $row->get('id').'">'. $row->get('name') .'</option>';
        }
        echo $output;
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
    public function addToFaculty(Request $request){
        $faculty = $request->get('faculty');
        $semester = $request->get('semester');
        $coordinator = $request->get('coordinator');
        $ad = new FacultySemester();
    }
}
