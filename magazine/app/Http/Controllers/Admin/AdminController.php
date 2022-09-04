<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Http\Requests\CreateFaculty;
use App\Models\Semester;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\FacultySemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        dd($sem);
    }
    public function store(Request $request)
    {
        $semester = new Semester();
        $semester->name = $request->get('name');
        $semester->description = $request->get('description');
        $semester->start_date = $request->get('start_date');
        $semester->end_date = $request->get('end_date');
        $OneYearLimit = Carbon::parse($semester->start_date)->addYear();
        if ($semester->end_date > $OneYearLimit)
        {
              return $this->responseMessage("The end date must not be more than 1 year from start date", true);
        }
         if ($semester->save())
        {
            return view('admin.Semester.create-semester')->with($this->responseBladeMessage('New semester created successfully'));
        }
        return back()->with($this->responseBladeMessage('Create unsuccessfully', false));
    }
    public function searchFaculty($semester, $request)
    {
        $search = FacultySemester::where('name', 'LIKE', '%' . $request . '%')
            ->where('semester_id', 'like', '%' . $semester . '%')
            ->get();
        return response()->json($search);
    }
    public function addStudentToFaculty()
    {
    }
    public function dashboard()
    {
        return view('admin.dashboard');
    }
}
