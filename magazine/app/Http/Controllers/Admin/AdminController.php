<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Http\Requests\CreateFaculty;
use App\Models\Semester;
use App\Models\Faculty;
use App\Models\Student;
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
    public function createFaculty(CreateFaculty $request)
    {
        $coor = new Faculty();
        $coor->semester_id= $request->get('semester_id');
        $coor->name = $request->get('name');
        $coor->first_deadline = $request->get('first_deadline');
        $coor->second_deadline = $request->get('second_deadline');
        $validated = $request->validated();
        $Sem = Semester::find($coor->semester_id); 
        $today = Carbon::now()->format('Y-m-d');
        if($Sem)
        {
        $DuplicateFaculty = Faculty::where('semester_id','=', Input::get('semester_id'))
        ->where('name','=',Input::get('name'))->first(); 
        $SemStartdate = Carbon::parse($Sem->start_date)->format('Y-m-d'); 
        $SemEnddate = Carbon::parse($Sem->end_date)->format('Y-m-d');
        }
        if(!$Sem) 
        {
            return $this->responseMessage('There is no semester with id '.$coor->semester_id. " .Please create one first", true);
        }
        if($coor->first_deadline < $SemStartdate || $coor->first_deadline > $SemEnddate
        ||$coor->second_deadline < $SemStartdate || $coor->second_deadline > $SemEnddate) 
        {
            return $this->responseMessage('Please enter the date between semester start and end date',true);
        }
        if(!empty($DuplicateFaculty))
          {
              return $this->responseMessage('Faculty '.$coor->name. ' of semester id '.$coor->semester_id. ' already exist',true);
          }
        if($SemStartdate <= $today || $SemEnddate <= $today)
        {
            return $this->responseMessage('cannot make new faculty in existing semester or past semester',true);
        }
        else if ($coor->save())
            {
                return $this->responseMessage(
                    'New faculty created successfully',
                    false,
                    'success',
                    $coor
                );
            }
        }
    public function searchFaculty($semester, $request)
    {
        $search = Faculty::where('name', 'LIKE', '%' . $request . '%')
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
