<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFaculty;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\FacultySemesterStudent;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        $listSemester = Semester::with("faculty_semester")
            ->where('start_date', ">", Carbon::now()->toDateTimeString())
            ->orderBy('start_date', 'asc')
            ->get();
        return view('admin.faculty.choose-semester', ['availableSemester' => $listSemester]);
    }
    public function createFaculty($semester)
    {
        $retrievedSemester = Semester::with("faculty_semester")->find($semester);
        return view('admin.faculty.create-faculty', ['semester' => $retrievedSemester]);
    }
    public function createFaculty_post(CreateFaculty $request)
    {
        $coor = new Faculty();
        $coor->name = $request->input('name');
        $DuplicateFaculty = Faculty::with("faculty_semester")
            ->where('name', '=', $request->name)->first();
        if (!empty($DuplicateFaculty)) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
        }
        if ($coor->save()) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
        }
    }
    public function updateFaculty(Request $request)
    {
        $faculty= Faculty::find($request->faculty_id);
        $faculty->name= $request->faculty_name;
        $DuplicateFaculty = Faculty::where('name','=',$request->faculty_name)->first();
        $request->validate([
            'faculty_name' => 'required|regex:/^[a-zA-Z\s]*$/'
        ]);
        if(!empty($DuplicateFaculty))
          {
            return back()->with(
                $this->responseBladeMessage('Faculty already exist. Please try again', false)
            );
          }
       else if ($faculty->save())
            {
                return back()->with(
                    $this->responseBladeMessage('Update faculty successfully.')
                );
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
                ->where(function (Builder $query) use ($searchTerms) {
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
        $semester = Semester::with("faculty_semester")->find($semester);
        $faculty = Faculty::all();
        $FacultySemester = DB::table('faculty_semesters')
            ->join('faculties', 'faculty_semesters.faculty_id', '=', 'faculties.id')
            ->select('faculties.name', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $semester->id)
            ->get();
        return view('admin.faculty.choose-semester-faculty', [
            'semester' => $semester,
            'faculty' => $faculty,
            'FacultySemester' => $FacultySemester,
        ]);
    }
    public function addSemesterFaculty_post($semester, $faculty)
    {
        $semester = Semester::with("faculty_semester")->find($semester);
        $faculty = Faculty::with("faculty_semester")->find($faculty);
        $FacuSeme = new FacultySemester;
        $FacuSeme->semester_id = $semester->id;
        $FacuSeme->faculty_id = $faculty->id;
        $FacuSeme->first_deadline = $semester->start_date;
        $FacuSeme->second_deadline = $semester->end_date;
        $FacuSeme->description = $semester->description;
        $duplicate = FacultySemester::with("faculty")
            ->where('semester_id', '=', $FacuSeme->semester_id)
            ->where('faculty_id', '=', $FacuSeme->faculty_id)
            ->first();
        if (!empty($duplicate)) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_duplicate'), false));
        } else if ($FacuSeme->save()) {
            return back()->with($this->responseBladeMessage(__('message.create_faculty_success')));
        }
        return back()->with($this->responseBladeMessage(__('message.create_faculty_failed'), false));
    }
    public function addStudentFaculty($facultysemester){
        $FacultySemester = FacultySemester::find($facultysemester);
        $semester = Semester::where('id','=',$FacultySemester->semester_id)->get();
        $faculty = Faculty::where('id','=',$FacultySemester->faculty_id)->get();
        $AvailableStudent = DB::table('faculty_semester_students')
            ->join('students', 'faculty_semester_students.student_id', '=', 'students.id')
            ->select('students.first_name','students.last_name','students.id')
            ->where('faculty_semester_students.faculty_semester_id','=',$FacultySemester->id)
            ->get();
            $StudentList = DB::table('students')
            ->leftjoin('faculty_semester_students', 'faculty_semester_students.student_id', '=', 'students.id')
            ->select('students.first_name','students.id','students.last_name')
            ->whereNull('faculty_semester_students.student_id')
            ->paginate(PER_PAGE);
            return view('admin.faculty.add-student', [
                'semester' => $semester,
                'faculty' => $faculty,
                'StudentList' => $StudentList,
                'AvailableStudent' => $AvailableStudent,
                'FacultySemester' => $FacultySemester,
            ]);
    }
    public function addStudentFaculty_post($FacultySemester, $student)
    {
        $student = Student::with("faculty_semester_student")->find($student);
        $FacuSemeStudent = new FacultySemesterStudent;
        $FacuSemeStudent->faculty_semester_id = $FacultySemester;
        $FacuSemeStudent->student_id = $student->id;
        if ($FacuSemeStudent->save())
            {
                return back()->with(
                    $this->responseBladeMessage('Add new student successful')
                );
            }
        return view('admin.faculty.add-student')
        ->with('FacuSemeStudent',$FacuSemeStudent);
    }
    public function deleteStudentFaculty($studentId)
    {
        $FacuSemeStudent = FacultySemesterStudent::where('student_id','=',$studentId);
        $FacuSemeStudent->delete();
        return back()->with(
            $this->responseBladeMessage('Delete successful')
        );
    }
    public function deleteSemesterFaculty(Request $request)
    {
        $hasStudent = FacultySemesterStudent::where('faculty_semester_id','=',$request->facu_seme_id)->first();
        if($hasStudent){
            $removeStudent = FacultySemesterStudent::where('faculty_semester_id','=',$request->facu_seme_id)->get()->each->delete();
            $FacuSeme = FacultySemester::findOrFail($request->facu_seme_id);
            $FacuSeme->delete();
            return back()->with(
                $this->responseBladeMessage('Delete successful, all students are removed')
            );
        }
        else{
            $FacuSeme = FacultySemester::find($request->facu_seme_id);
            $FacuSeme->delete();
            return back()->with(
                $this->responseBladeMessage('Delete successful, no student has been deleted')
            );
        }
    }
    public function searchFaculty($semester, $request)
    {
        $search = Faculty::with("faculty_semester")
            ->where('name', 'LIKE', '%' . $request . '%')
            ->where('semester_id', 'like', '%' . $semester . '%')
            ->get();
        return response()->json($search);
    }
}
