<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\FacultySemester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $selectedMode = $request->get('viewMode');
        $searchTerms = $request->get('search_faculty_input');
        $listFaculty = FacultySemester::with(['faculty'])
            ->whereHas('faculty_semester_student.student', function ($query) {
                $query->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            });
        if ($selectedMode) {
            switch ($selectedMode) {
                case '1':
                    $listFaculty->whereHas('semester', function ($query) {
                        $query->whereDate('start_date', ">", Carbon::now()->toDateTimeString())
                            ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());;
                    });
                    break;
                case '2':
                    $listFaculty->whereHas('semester', function ($query) {
                        $query->whereDate('end_date', "<=", Carbon::now()->toDateTimeString())
                            ->whereDate('start_date', "<=", Carbon::now()->toDateTimeString());
                    });
                    break;
                default:
                    $selectedMode = '0';
            }
        } else {
            $selectedMode = '0';
        }
        if ($searchTerms) {
            $listFaculty->whereHas('faculty', function ($query) use ($searchTerms) {
                $query->where('name', 'like', '%' . $searchTerms . '%')
                    ->orWhereHas('faculty_semester.semester', function ($query) use ($searchTerms) {
                        $query->where('end_date', "like", '%' . $searchTerms . '%');
                    });
            });
        }
        $currentFaculty = FacultySemester::with(['faculty_semester_student.student'])
            ->whereHas('faculty_semester_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($query) {
                $query->whereDate('start_date', "<", Carbon::now()->toDateTimeString())
                    ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());
            })
            ->first();
        return view('student.faculty.faculties', [
            'viewMode' => $selectedMode,
            'searchTerms' => $searchTerms,
            'semester_faculties' => $listFaculty
                ->orderBy('semester_id', 'desc')
                ->paginate(PER_PAGE),
            'currentFaculty' => $currentFaculty
        ]);
    }
    public function facultyDetailDashboard($id, $semester)
    {
        return $this->facultyDetail($id, $semester, 'student.faculty.faculty-detail-dashboard');
    }
	public function facultyDetailMember($id, $semester)
	{
		return $this->facultyDetail($id, $semester, 'student.faculty.faculty-detail-member', false);
	}
	private function facultyDetail($id, $semester, $view, $facultyPage = true){
		$faculty = FacultySemester::with(['faculty'])
			->where('id', $semester)
			->whereHas('faculty', function ($q) use ($id){
				$q->where('id', $id);
			})
			->whereHas('faculty_semester_student.student', function ($q) {
				$q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
			})->first();
		if ($faculty)
			return view($view, [
				'facultySemester' => $faculty,
				'isDashboard' => $facultyPage
			]);
		else
			return redirect()->route('student.faculty');
	}
}
