<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Models\Coordinator;
use App\Models\FacultySemesterCoordinator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
        ->whereHas("faculty_semester.semester", function ($query){
            $query->where("start_date", "<=", Carbon::now()->toDateTimeString())
                ->where("end_date", ">=", Carbon::now()->toDateTimeString());
        })->first();
        return view('coordinator.Semester.semester', [
            'facSemesterCoordinator' => $facultySemesterCoordinator]);
    }
}
