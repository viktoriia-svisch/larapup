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
         if ($ad->end_date > $OneYearLimit){
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
        $checksemesterId = \DB::table('semesters') 
        ->select('semesters.id')
        ->where('semesters.id',$coor->semester_id)
        ->first();
        $getSemesterDate = \DB::table('semesters') 
        ->where('id','=',$coor->semester_id)
        ->first();
        if(!$checksemesterId) 
        {
            return $this->responseMessage('There is no semester with id '.$coor->semester_id. " .Please create one first", true);
        }
        if($coor->first_deadline < $getSemesterDate->start_date || $coor->first_deadline > $getSemesterDate->end_date) 
         {
              return $this->responseMessage('Please enter the date between semester start and end date',true);
         }
        if($coor->second_deadline < $getSemesterDate->start_date || $coor->second_deadline > $getSemesterDate->end_date)
         {
              return $this->responseMessage('Please enter the date between semester start and end date',true);
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
            ->where('status', STUDENT_STATUS['ONGOING'])
            ->get();
        return view('admin.student.student', [
            'availableStudent' => $studentList
        ]);
    }
}
