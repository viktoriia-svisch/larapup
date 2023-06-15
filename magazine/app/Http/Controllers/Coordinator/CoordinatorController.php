<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCoordinatorAccount;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
}
