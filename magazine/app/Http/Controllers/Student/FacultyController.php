<?php
namespace App\Http\Controllers\Student;
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
        $listFaculty = FacultySemester::with(['faculty'])
            ->whereHas('faculty_semester_student.student', function (Builder $query) {
                $query->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
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
                $query->where('name', 'like', "%$searchTerms%")
                    ->orWhereHas('faculty_semester.semester', function (Builder $query) use ($searchTerms) {
                        $query->where('end_date', "like", '%' . $searchTerms . '%');
                    });
            });
        }
        $currentFaculty = FacultySemester::with(['faculty_semester_student.student'])
            ->whereHas('faculty_semester_student.student', function (Builder $q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })
            ->whereHas('semester', function (Builder $query) {
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
    public function facultyDetailArticle($faculty_id, $semester_id)
    {
        $article = Article::with('article_file')
            ->whereHas('faculty_semester', function (Builder $query) use ($faculty_id) {
                $query->where("faculty_id", $faculty_id);
            })
            ->where('student_id', Auth::guard(STUDENT_GUARD)->user()->id)
            ->first();
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-article', "article", ["article" => $article]);
    }
    private function facultyDetail($faculty_id, $semester, $view, $site = 'dashboard', $extData = [])
    {
        $faculty = FacultySemester::with(['faculty'])
            ->where('semester_id', $semester)
            ->whereHas('faculty', function (Builder $q) use ($faculty_id) {
                $q->where('id', $faculty_id);
            })
            ->whereHas('faculty_semester_student.student', function (Builder $q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
            })->first();
        $article = Article::with("student")
            ->where("student_id", Auth::guard(STUDENT_GUARD)->user()->id)
            ->whereHas("faculty_semester", function (Builder $query) use ($faculty) {
                $query->where("id", $faculty->id);
            })->first();
        switch ($site) {
            case "member":
                $isDashboard = false;
                $isArticle = false;
                break;
            case "article":
                $isDashboard = false;
                $isArticle = true;
                break;
            default:
                $isDashboard = true;
                $isArticle = false;
        }
        $data = [
            'facultySemester' => $faculty,
            "article" => $article,
            'isDashboard' => $isDashboard,
            'isArticle' => $isArticle
        ];
        if (count($extData) > 0) {
            $data = array_merge($data, $extData);
        }
        if ($faculty)
            return view($view, $data);
        else
            return redirect()->route('student.faculty');
    }
    public function facultyDetailDashboard($faculty_id, $semester_id)
    {
        $commentStudent = CommentStudent::with("student")
            ->where("student_id", Auth::guard(STUDENT_GUARD)->user()->id)
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            })
            ->get();
        $commentCoordinator = CommentCoordinator::with("coordinator")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            })
            ->select(DB::raw("id, article_id, coordinator_id as user_id, content, image_path, notified, created_at, updated_at, 'coordinator' as table_name"))
            ->get()->merge($commentStudent)->sortByDesc("created_at");
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-dashboard', "dashboard", [
            "comments" => $commentCoordinator
        ]);
    }
    public function facultyDetailMember($faculty_id, $semester_id)
    {
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-member', "member");
    }
}
