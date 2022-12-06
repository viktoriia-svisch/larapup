<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CommentCoordinator;
use App\Models\CommentStudent;
use App\Models\FacultySemester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $selectedMode = $request->get('viewMode');
        $searchTerms = $request->get('search_faculty_input');
        $listFaculty = FacultySemester::with(['faculty_semester_coordinator'])
            ->whereHas('faculty_semester_coordinator.coordinator', function (Builder $query) {
                $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            });
        if ($selectedMode) {
            switch ($selectedMode) {
                case '1':
                    $listFaculty->whereHas('semester', function (Builder $query) {
                        $query->whereDate('start_date', ">", Carbon::now()->toDateTimeString())
                            ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());;
                    });
                    break;
                case '2':
                    $listFaculty->whereHas('semester', function (Builder $query) {
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
            $listFaculty->whereHas('faculty', function (Builder $query) use ($searchTerms) {
                $query->where('name', 'like', '%' . $searchTerms . '%')
                    ->orWhereHas('faculty_semester.semester', function (Builder $query) use ($searchTerms) {
                        $query->where('end_date', "like", '%' . $searchTerms . '%');
                    });
            });
        }
        $currentFaculty = FacultySemester::with(['faculty_semester_student.student'])
            ->whereHas('faculty_semester_coordinator.coordinator', function (Builder $q) {
                $q->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->whereHas('semester', function (Builder $query) {
                $query->whereDate('start_date', "<", Carbon::now()->toDateTimeString())
                    ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());
            })
            ->first();
        return view('coordinator.Faculty.faculties', [
            'viewMode' => $selectedMode,
            'searchTerms' => $searchTerms,
            'faculties' => $listFaculty
                ->orderBy('semester_id', 'desc')
                ->paginate(PER_PAGE),
            'currentFaculty' => $currentFaculty
        ]);
    }
    public function facultyDetailArticle($faculty_id, $semester_id)
    {
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-article', "article");
    }
    private function facultyDetail($faculty_id, $semester, $view, $site = 'dashboard', $extData = [])
    {
        $faculty = FacultySemester::with(['faculty'])
            ->where('semester_id', $semester)
            ->whereHas('faculty', function (Builder $q) use ($faculty_id) {
                $q->where('id', $faculty_id);
            })
            ->whereHas('faculty_semester_coordinator.coordinator', function (Builder $q) {
                $q->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })->first();
        switch ($site) {
            case "dashboard":
            case "published":
            case "articles":
            case "members":
            case "settings":
                break;
            default:
                $site = "dashboard";
        }
        $data = [
            'facultySemester' => $faculty,
            "site" => $site
        ];
        if (count($extData) > 0) {
            $data = array_merge($data, $extData);
        }
        if ($faculty)
            return view($view, $data);
        else
            return redirect()->route('coordinator.faculty');
    }
    public function facultyDetailDashboard($faculty_id, $semester_id)
    {
        return $this->facultyDetail($faculty_id, $semester_id, 'coordinator.Faculty.faculty-detail-dashboard', "dashboard", [
        ]);
    }
    public function facultyDetailMember($faculty_id, $semester_id)
    {
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-member', "member");
    }
    public function facultyDetailSettings($faculty_id, $semester_id){
        return 1;
    }
}
