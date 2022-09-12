<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Http\Resources\Faculty as FacultyResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
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
    public function searchFaculty(Request $request)
    {
        $searchTerms = $request->get('search_faculty_input');
        if ($searchTerms) {
            $faculties = Faculty::with('faculty_semester')
                ->where('name', 'LIKE', '%' . $searchTerms . '%')
                ->paginate(PER_PAGE);
            return view('coordinator.manage-coordinator', [
                'faculties' => $faculties,
                'searching' => $searchTerms
            ]);
        }
        $faculties = Faculty::with('faculty_semester')
            ->paginate(PER_PAGE);
        return view('coordinator.manage-coordinator', [
            'faculties' => $faculties,
            'searching' => false
        ]);
    }
    public function dashboard(){
        return view('coordinator.dashboard');
    }
    public function search($request){
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
}
