<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Http\Requests\CreateFaculty;
use App\Models\Semester;
use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Http\Resources\Semester as SemesterResource;
use App\Http\Resources\Faculty as FacultyResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        return SemesterResource::collection($sem);
    }
    public function createSemester(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $ad->end_date = $request->get('end_date');
        $validated = $request->validated();
        $OneYearLimit = Carbon::parse($ad->start_date)->addYear(); 
        if ($ad->end_date > $OneYearLimit)
        {
              return $this->responseMessage("The end date must not be more than 1 year from start date", true);
        }
         if ($ad->save())
        {
            return $this->responseMessage(
                'New semester created successfully',
                false,
                'success',
                $ad
            );
        }
        return $this->responseMessage('Create unsuccessfully', true);
        return view('admin.Semester.create-semester');
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
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    public function searchFaculty($id)
    {
    }
    public function semester()
    {
        $semesters = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator']);
        return view('admin.Semester.semester', [
            'listSemester' => $semesters
        ]);
    }
    public function student(Request $request)
    {
        $studentList = Student::with('faculty_student.faculty')
            ->whereDoesntHave('faculty_student.faculty', function ($q) {
                $q->whereHas('semester', function ($q) {
                    $q->where('end_date', '>=', Carbon::now()->toDateString(DATE_FORMAT));
                });
            })
            ->get();
        return view('admin.student.student', [
            'availableStudent' => $studentList
        ]);
    }
    public function createStudent(){
        return view('admin.student.create-student');
    }
    public function createStudent_post(Request $request){
        $student = new Student($request->all([
            'email',
            'password',
            'first_name',
            'last_name',
            'gender',
            'dateOfBirth'
        ]));
        if ($student->save())
            return redirect()->back()->with([
                'success' => true
            ]);
        return redirect()->back()->with([
            'success' => false
        ]);
    }
}
