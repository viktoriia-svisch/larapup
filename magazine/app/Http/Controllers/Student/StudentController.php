<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudent;
use App\Http\Requests\UpdateStudentAccount;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class StudentController extends Controller
{
    public function updateStudent($id)
    {
        $student = Student::with("faculty_semester_student")->find($id);
        return view('student.manage.student-detail', [
            'student' => $student]);
    }
    public function updateStudentPost(UpdateStudentAccount $request, $id)
    {
        $student = Student::with("faculty_semester_student")->find($id);
        if (!$student) return redirect()->back()->withInput()->with(
            $this->responseBladeMessage("The student information is invalid", false)
        );
        DB::beginTransaction();
        $student->first_name = $request->get('first_name') ?? $student->first_name;
        $student->last_name = $request->get('last_name') ?? $student->last_name;
        $student->dateOfBirth = $request->get('dateOfBirth') ?? $student->dateOfBirth;
        $student->gender = $request->get('gender') ?? $student->gender;
        if ($request->get('old_password')) {
            if (Hash::check($request->get('old_password'), $student->password)) {
                $student->password = $request->get('new_password');
            } else {
                DB::rollBack();
                return back()->with(
                    $this->responseBladeMessage("The old password entered is incorrect!", false)
                );
            }
        }
        if ($student->save()) {
            DB::commit();
            return back()->with(
                $this->responseBladeMessage("Update successfully!")
            );
        }
        DB::rollBack();
        return back()->with(
            $this->responseBladeMessage("Update failed. Cannot save the new data!", false)
        );
    }
    public function article()
    {
        return view('shared.article');
    }
    public function dashboard()
    {
        $currentSemester = Semester::with('faculty_semester')
            ->where('start_date', '<=', Carbon::now()->toDateTimeString())
            ->whereHas('faculty_semester.faculty_semester_student.student', function (Builder $q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->where('end_date', '>', Carbon::now()->toDateTimeString())
            ->first();
        $currentFaculty = Faculty::with('faculty_semester.semester')
            ->whereHas('faculty_semester.faculty_semester_student.student', function (Builder $q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('faculty_semester.semester', function (Builder $q) {
                $q->where('start_date', '<=', Carbon::now()->toDateTimeString())
                    ->where('end_date', '>', Carbon::now()->toDateTimeString());
            })
            ->first();
        $currentActiveData = FacultySemester::with(['faculty', 'semester'])
            ->whereHas('faculty_semester_student.student', function (Builder $query) {
                $query->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function (Builder $query) {
                $query->where('start_date', '<=', Carbon::now()->toDateTimeString())
                    ->where('end_date', '>', Carbon::now()->toDateTimeString());
            })
            ->orderBy('second_deadline')->first();
        return view('student.dashboard', [
            'activeData' => $currentActiveData,
            'activeSemester' => null,
            'activeFaculty' => null
        ]);
    }
    public function store(CreateStudent $request)
    {
        $std = new Student();
        $std->email = $request->get('email');
        $std->password = $request->get('password');
        $std->firstname = $request->get('first_name');
        $std->lastname = $request->get('last_name');
        $std->status = 1;
        if ($std->save())
            return $this->responseMessage(
                'New student created successfully',
                false,
                'success',
                $std
            );
        return $this->responseMessage('Create unsuccessfully', true);
    }
}
