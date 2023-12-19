<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCoordinator;
use App\Http\Requests\UpdateCoordinatorByAdmin;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
class CoordinatorController extends Controller
{
    public function coordinator(Request $request)
    {
        $searchTerms = $request->get('search_coordinator_input');
        $searchType = $request->get('type');
        $coordinatorList = Coordinator::with('faculty_semester_coordinator');
        if ($searchType != -1 && $searchType != null) {
            $coordinatorList->where('status', $request->get('type'));
        }
        if ($searchTerms != null) {
            $coordinatorList->where(function (Builder $query) use ($searchTerms) {
                $query->where('first_name', 'like', '%' . $searchTerms . '%')
                    ->orwhere('last_name', 'like', '%' . $searchTerms . '%');
            });
        }
        return view('admin.Coordinator.coordinator', ['coordinators' => $coordinatorList->paginate(PER_PAGE)]);
    }
    public function addToFaculty_index()
    {
        $faculty = Faculty::with("faculty_semester")->get();
        return view('admin.faculty.add-coordinator',
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
            $coors = Coordinator::whereDoesntHave("faculty_semester_coordinator",
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
                        <a href="' . route('admin.addToFaculty.addCoorToFaculty_post', ['faculty' => $faculty, 'semester' => $semester, 'coordinator' => $coor->id]) . '" class="col-xl-4 submit-coordinator">Add</a>
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
                        <a href="' . route('admin.addToFaculty.removeCoorFromFaculty_post', ['faculty' => $faculty, 'semester' => $semester, 'coordinator' => $coor->id]) . '" class="col-xl-4 submit-coordinator">Remove</a>
                      </div>
                    </div>
                  </div>
                </div>';
            }
            $output = ['availableCoor' => $availableCoor, 'unavailableCoor' => $unavailableCoor];
        }
        return $output;
    }
    public function createCoordinator_post(CreateCoordinator $request)
    {
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
    public function addToFaculty($coordinator, $faculty, $semester)
    {
        $faculty_semester = FacultySemester::where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester)->first();
        $coor = Coordinator::where('id', '=', $coordinator);
        $ad = new FacultySemesterCoordinator();
        $ad->faculty_semester_id = $faculty_semester->id;
        $ad->coordinator_id = $coordinator;
        if(!$faculty_semester || !$coor){
            return redirect()->back()->with($this->responseBladeMessage("add coordinator fail! - coordinator does not exist!",false));
        }
        if ($ad->save()) {
            return redirect()->back()->with($this->responseBladeMessage("add coordinator success!",true));
        }
        return redirect()->back()->with($this->responseBladeMessage("add coordinator fail!",false));
    }
    public function removeToFaculty($coordinator, $faculty, $semester)
    {
        $faculty_semester_coordinator = FacultySemesterCoordinator::where('coordinator_id', '=', $coordinator)
            ->whereHas("faculty_semester", function (Builder $builder) use($faculty, $semester){
                $builder->where('faculty_id', '=', $faculty)->where('semester_id', '=', $semester);
            })
            ->first();
        if(!$faculty_semester_coordinator){
            return redirect()->back()->with($this->responseBladeMessage("remove coordinator fail! - coordinator does not exist!",false));
        }
        if ($faculty_semester_coordinator->delete()) {
            return redirect()->back()->with($this->responseBladeMessage("remove coordinator success!",true));
        }
        return redirect()->back()->with($this->responseBladeMessage("remove coordinator fail!",false));
    }
    public function updateCoordinator($id)
    {
        $coordinator = Coordinator::with("faculty_semester_coordinator")->find($id);
        if ($coordinator)
        return view('admin.Coordinator.update-coordinator', [
            'coordinator' => $coordinator
        ]);
        return redirect()->back()->with($this->responseBladeMessage("Unable to find the coordinator", false));
    }
    public function updateCoordinatorPost(UpdateCoordinatorByAdmin $request, $id)
    {
        $coordinator = Coordinator::with("faculty_semester_coordinator")->find($id);
        if (!$coordinator)
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the coordinator", false));
        $coordinator->first_name = $request->get('first_name') ?? $coordinator->first_name;
        $coordinator->last_name = $request->get('last_name') ?? $coordinator->last_name;
        $coordinator->dateOfBirth = $request->get('dateOfBirth') ?? $coordinator->dateOfBirth;
        $coordinator->gender = $request->get('gender') ?? $coordinator->gender;
        $coordinator->status = $request->get('status') ?? $coordinator->status;
        $coordinator->type = $request->get('type') ?? $coordinator->type;
        if ($request->get('old_password')) {
            if (Hash::check($request->get('old_password'), $coordinator->password)) {
                $coordinator->password = $request->get('new_password');
            } else {
                return back()->with($this->responseBladeMessage("Password was incorrect!", false));
            }
        }
        if ($coordinator->save()) {
            return back()->with($this->responseBladeMessage("Updated successfully"));
        }
        return back()->with($this->responseBladeMessage("Update unsuccessfully", false));
    }
}
