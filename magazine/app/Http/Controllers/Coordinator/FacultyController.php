<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\Article;
use App\Models\FacultySemester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class FacultyController extends FacultySemesterBaseController
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
    public function facultyDetailDashboard($faculty_id, $semester_id)
    {
        return $this->facultyDetail($faculty_id, $semester_id, 'coordinator.Faculty.faculty-detail-dashboard', "dashboard", [
        ]);
    }
    public function facultyDetailMember($faculty_id, $semester_id)
    {
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-member', "member");
    }
    public function facultyDetailSettings($faculty_id, $semester_id)
    {
        return 1;
    }
    public function facultyDetailListArticle(Request $request, $faculty_id, $semester_id)
    {
        $articles = Article::with('article_file')
            ->whereHas('faculty_semester', function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        $searchStudent = $request->get("student_name");
        if ($searchStudent) {
            $articles->whereHas("student", function (Builder $student) use ($searchStudent) {
                $student->where("first_name", "like", "%$searchStudent%")
                    ->orWhere("last_name", "like", "%$searchStudent%")
                    ->orWhere("email", "like", "%$searchStudent%");
            });
        }
        $articles = $articles->paginate(PER_PAGE);
        return $this->facultyDetail($faculty_id, $semester_id, 'coordinator.Faculty.Articles.faculty-detail-listArticle', "articles", ["articles" => $articles]);
    }
    public function facultyDetailArticlePublish(Request $request, $faculty_id, $semester_id, $article_id)
    {
        $facultySemester = $this->retrieveFacultySemester($faculty_id, $semester_id);
        $article = $this->retrieveDetailArticle($faculty_id, $semester_id, $article_id);
        if ($article && $facultySemester)
            return view("coordinator.Faculty.Articles.faculty-detail-publishing", [
                "facultySemester" => $facultySemester,
                "article" => $article
            ]);
        return redirect()->route("coordinator.faculty.listArticle");
    }
}
