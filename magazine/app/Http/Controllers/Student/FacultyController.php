<?php
namespace App\Http\Controllers\Student;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Http\Requests\UploadArticleRequest;
use App\Models\Article;
use App\Models\ArticleFile;
use App\Models\FacultySemester;
use Exception;
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
    public function facultyDetailArticle($faculty_id, $semester_id)
    {
        $article = Article::with('article_file')
            ->whereHas('faculty_semester', function ($query) use ($faculty_id) {
                $query->where("faculty_id", $faculty_id);
            })
            ->where('student_id', Auth::guard(STUDENT_GUARD)->user()->id)
            ->first();
        return $this->facultyDetail($faculty_id, $semester_id, 'student.faculty.faculty-detail-article', "article", ["article" => $article]);
    }
    private function facultyDetail($id, $semester, $view, $site = 'dashboard', $extData = [])
    {
        $faculty = FacultySemester::with(['faculty'])
            ->where('semester_id', $semester)
            ->whereHas('faculty', function ($q) use ($id) {
                $q->where('id', $id);
            })
            ->whereHas('faculty_semester_student.student', function ($q) {
                $q->where('id', Auth::guard(STUDENT_GUARD)->user()->id);
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
    public function facultyDetailDashboard($id, $semester)
    {
        return $this->facultyDetail($id, $semester, 'student.faculty.faculty-detail-dashboard', "dashboard");
    }
    public function facultyDetailMember($id, $semester)
    {
        return $this->facultyDetail($id, $semester, 'student.faculty.faculty-detail-member', "member");
    }
    public function articleFilePost(UploadArticleRequest $request)
    {
        DB::beginTransaction();
        $article = Article::with("student")->firstOrCreate([
            "student_id" => Auth::id(),
            "faculty_semester_id" => $request->get("faculty_semester_id")
        ]);
        if ($article) {
            $files = $request->file("wordDocument");
            $arrNew = [];
            foreach ($files as $file) {
                try {
                    $filePath = StorageHelper::saveArticle($article->id, $file);
                } catch (Exception $exception) {
                    DB::rollback();
                    return back()->with($this->responseBladeMessage(
                        "Cannot store file in the system",
                        false
                    ));
                }
                $articleFile = new ArticleFile();
                $articleFile->title = $file->getClientOriginalName();
                $articleFile->file_path = $filePath;
                $extIndex = 0;
                foreach (FILE_EXT as $ext) {
                    if (strcasecmp($file->getClientOriginalExtension(), $ext)) {
                        $extIndex = array_search($ext, FILE_EXT);
                        break;
                    }
                }
                $articleFile->type = $extIndex;
                array_push($arrNew, $articleFile);
            }
            if ($article->article_file()->saveMany($arrNew)) {
                $mailService = new MailController();
                $coordinator = $article->faculty_semester->faculty_semester_coordinator[0]->coordinator;
                $mailService->sendGradingEmail(
                    $coordinator->email, $coordinator,
                    $article->faculty_semester->faculty_id,
                    $article->faculty_semester->semester_id
                );
                DB::commit();
                return back()->with($this->responseBladeMessage("Upload successfully!"));
            }
        }
        DB::rollback();
        return back()->with($this->responseBladeMessage("Cannot initialize the article data!", false));
    }
}
