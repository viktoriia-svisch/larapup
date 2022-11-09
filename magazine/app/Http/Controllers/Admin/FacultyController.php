<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFaculty;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\Student;
use App\Models\FacultySemester;
use App\Models\FacultySemesterStudent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $searchTerms = $request->get('search_faculty_input');
        if ($searchTerms) {
            $faculties = Faculty::with('faculty_semester')
                ->where('name', 'LIKE', '%' . $searchTerms . '%')
                ->paginate(PER_PAGE);
            return view('admin.faculty.faculty', [
                'faculties' => $faculties,
                'searching' => $searchTerms
            ]);
        }
        $faculties = Faculty::with('faculty_semester')
            ->paginate(PER_PAGE);
        return view('admin.faculty.faculty', [
            'faculties' => $faculties,
            'searching' => false
        ]);
    }
    public function createFaculty_semester()
    {
        $listSemester = Semester::
        where('start_date', ">", Carbon::now()->toDateTimeString())
            ->orderBy('start_date', 'asc')
            ->get();
        return view('admin.faculty.choose-semester', ['availableSemester' => $listSemester]);
    }
    public function createFaculty($semester)
    {
        $retrievedSemester = Semester::find($semester);
        return view('admin.faculty.create-faculty', ['semester' => $retrievedSemester]);
    }
    public function createFaculty_post(CreateFaculty $request)
    {
        $coor = new Faculty;
        $coor->name = $request->input('name');
        $DuplicateFaculty = Faculty::where('name','=',$request->name)->first();
        if(!empty($DuplicateFaculty))
          {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
          }
        if ($coor->save())
            {
                return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
            }
    }
    public function chooseSemester(Request $request)
    {
        $searchTerms = $request->get('search_semester_input');
        $searching = false;
        if ($searchTerms) {
            $searching = $searchTerms;
            $semestersFuture = Semester::with(['faculty_semester'])
                ->where('start_date', '>=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $semestersFuture = Semester::with(['faculty_semester'])
                ->where('start_date', '>=', Carbon::now())
                ->orderBy('start_date', 'desc')
                ->get();
        }
        return view('admin.faculty.choose-semester', [
            'futureSemester' => $semestersFuture,
            'searching' => $searching
        ]);
    }
    public function chooseSemesterFaculty($semester)
    {
        $semester = Semester::find($semester);
        $faculty = Faculty::all();
        $StudentList = Student::all();
        $FacultySemester = DB::table('faculty_semesters')
            ->join('faculties', 'faculty_semesters.faculty_id', '=', 'faculties.id')
            ->select('faculties.name','faculty_semesters.id')
            ->where('faculty_semesters.semester_id','=',$semester->id)
            ->get();
        return view('admin.faculty.choose-semester-faculty')
        ->with('semester',$semester)
        ->with('faculty',$faculty)
        ->with('StudentList',$StudentList)
        ->with('FacultySemester',$FacultySemester);
    }
    public function addSemesterFaculty_post($semester,$faculty)
    {
        $semester = Semester::find($semester);
        $faculty = Faculty::find($faculty);
        $FacuSeme = new FacultySemester;
        $FacuSeme->semester_id = $semester->id;
        $FacuSeme->faculty_id = $faculty->id;
        $FacuSeme->first_deadline = $semester->start_date;
        $FacuSeme->second_deadline = $semester->end_date;
        $FacuSeme->description = $semester->description;
        $duplicate = FacultySemester::where('semester_id','=',$FacuSeme->semester_id)
        ->where('faculty_id','=',$FacuSeme->faculty_id)->first();
        if(!empty($duplicate))
          {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
          }
        else if ($FacuSeme->save())
            {
                return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
            }
    }
    public function addStudentFaculty($facultysemester){
        $FacultySemester = FacultySemester::find($facultysemester);
        $semester = Semester::where('id','=',$FacultySemester->semester_id)->get();
        $faculty = Faculty::where('id','=',$FacultySemester->faculty_id)->get();
        $StudentList = Student::all();
        $AvailableStudent = DB::table('faculty_semester_students')
            ->join('students', 'faculty_semester_students.student_id', '=', 'students.id')
            ->select('students.first_name','students.last_name')
            ->where('faculty_semester_students.faculty_semester_id','=',$FacultySemester->id)
            ->get();
        return view('admin.faculty.add-student')
        ->with('semester',$semester)
        ->with('faculty',$faculty)
        ->with('StudentList',$StudentList)
        ->with('AvailableStudent',$AvailableStudent)
        ->with('FacultySemester',$FacultySemester);
    }
    public function addStudentFaculty_post($FacultySemester,$student){
        $student = Student::find($student);
        $FacuSemeStudent = new FacultySemesterStudent;
        $FacuSemeStudent->faculty_semester_id = $FacultySemester;
        $FacuSemeStudent->student_id = $student->id;
        $HasFaculty = FacultySemesterStudent::where('student_id','=',$student->id)->first();
        if(!empty($HasFaculty))
        {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
        }
        if ($FacuSemeStudent->save())
            {
                return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
            }
    }
    public function searchFaculty($semester, $request)
    {
        $search = Faculty::where('name', 'LIKE', '%' . $request . '%')
            ->where('semester_id', 'like', '%' . $semester . '%')
            ->get();
        return response()->json($search);
    }
}
