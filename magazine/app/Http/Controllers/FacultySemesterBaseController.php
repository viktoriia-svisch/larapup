<?php
namespace App\Http\Controllers;
use App\Helpers\StorageHelper;
use App\Models\Article;
use App\Models\CommentCoordinator;
use App\Models\CommentStudent;
use App\Models\Coordinator;
use App\Models\FacultySemester;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use ZipArchive;
class FacultySemesterBaseController extends Controller
{
    public function facultyDetail($faculty_id, $semester_id, $view, $site = 'dashboard', $extData = [], $guard = COORDINATOR_GUARD, $redirectRoute = 'coordinator.faculty')
    {
        $facultySemester = $this->retrieveFacultySemester($faculty_id, $semester_id, $guard);
        switch ($site) {
            case "dashboard":
            case "published":
            case "articles":
            case "article":
            case "members":
            case "settings":
                break;
            default:
                $site = "dashboard";
        }
        $data = [
            'facultySemester' => $facultySemester,
            "site" => $site
        ];
        if (count($extData) > 0) {
            $data = array_merge($data, $extData);
        }
        if ($facultySemester)
            return view($view, $data);
        else
            return redirect()->route($redirectRoute);
    }
    public function retrieveFacultySemester($faculty_id, $semester_id, $guard = COORDINATOR_GUARD)
    {
        $faculty = FacultySemester::with(['faculty'])
            ->where('semester_id', $semester_id)
            ->where('faculty_id', $faculty_id);
        if ($guard == COORDINATOR_GUARD) {
            $faculty = $faculty->whereHas('faculty_semester_coordinator.coordinator', function (Builder $q) use ($guard) {
                $q->where('id', Auth::guard($guard)->user()->id);
            });
        } elseif ($guard == STUDENT_GUARD) {
            $faculty = $faculty->whereHas('faculty_semester_student.student', function (Builder $q) use ($guard) {
                $q->where('id', Auth::guard($guard)->user()->id);
            });
        }
        return $faculty->first();
    }
    public function retrieveCurrentSemester($search = null, $idSemester = null, $retrieveOne = true)
    {
        $semestersActive = Semester::with(['faculty_semester']);
        if ($idSemester != null) {
            $semestersActive = $semestersActive->find($idSemester);
        } else {
            if ($search) {
                $semestersActive = $semestersActive->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('start_date', 'like', '%' . $search . '%')
                        ->orWhere('end_date', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            } else {
                $semestersActive = $semestersActive
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now());
            }
            if ($retrieveOne) {
                $semestersActive = $semestersActive->first();
            } else {
                $semestersActive = $semestersActive->get();
            }
        }
        return $semestersActive;
    }
    public function retrieveFacultySemesterMembers(Request $request, $faculty_id, $semester_id)
    {
        $search = $request->get("search");
        $students = Student::with("faculty_semester_student")
            ->whereHas("faculty_semester_student.faculty_semester", function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        $coordinators = Coordinator::with("faculty_semester_coordinator")
            ->whereHas("faculty_semester_coordinator.faculty_semester", function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        if ($search) {
            $students = $students->where(function (Builder $builder) use ($search) {
                $builder->where("first_name", "like", "%$search%")
                    ->orWhere("last_name", "like", "%$search%")
                    ->orWhere("email", "like", "%$search%");
            });
            $coordinators = $coordinators->where(function (Builder $builder) use ($search) {
                $builder->where("first_name", "like", "%$search%")
                    ->orWhere("last_name", "like", "%$search%")
                    ->orWhere("email", "like", "%$search%");
            });
        }
        $arrData = [
            "students" => $students->paginate(PER_PAGE),
            "coordinators" => $coordinators->get(),
            "search" => $search,
        ];
        return $arrData;
    }
    public function retrieveDetailArticle($faculty_id, $semester_id, $article_id)
    {
        return Article::with("student")->whereHas("faculty_semester", function (Builder $builder) use ($faculty_id, $semester_id) {
            $builder->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
        })->where("id", $article_id)->first();
    }
    public function retrieveDetailArticleByStudent($faculty_id, $semester_id, $student_id)
    {
        return Article::with("student")
            ->whereHas("faculty_semester", function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            })
            ->where("student_id", $student_id)->first();
    }
    public function retrieveCommentAll($faculty_id, $semester_id, $guardStudent = STUDENT_GUARD, $guardCoordinator = COORDINATOR_GUARD, $article_id = null)
    {
        $commentStudent = $this->retrieveCommentStudent($faculty_id, $semester_id, $guardStudent, $article_id);
        $commentCoordinator = $this->retrieveCommentCoordinator($faculty_id, $semester_id, $guardCoordinator, $article_id);
        $arrCombined = $commentStudent->concat($commentCoordinator)->sortByDesc(function ($comment) {
            return $comment['created_at'];
        });
        return $arrCombined;
    }
    public function retrieveCommentStudent($faculty_id, $semester_id, $guard = STUDENT_GUARD, $article_id = null)
    {
        $commentStudent = CommentStudent::with("student")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        if ($guard == STUDENT_GUARD) {
            $commentStudent = $commentStudent->where("student_id", Auth::guard($guard)->user()->id);
        }
        if ($article_id) {
            $commentStudent = $commentStudent->where("article_id", $article_id);
        }
        return $commentStudent->get();
    }
    public function retrieveCommentCoordinator($faculty_id, $semester_id, $guard = COORDINATOR_GUARD, $article_id = null)
    {
        $commentCoordinator = CommentCoordinator::with("coordinator")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        if ($guard == COORDINATOR_GUARD) {
            $commentCoordinator = $commentCoordinator->where("coordinator_id", Auth::guard($guard)->user()->id);
        }
        if ($article_id) {
            $commentCoordinator = $commentCoordinator->where("article_id", $article_id);
        }
        return $commentCoordinator->get();
    }
    public function downloadArticleFaculty($faculty_id, $semester_id)
    {
        $listArticle = Article::with('student')
            ->whereHas('faculty_semester', function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where('faculty_id', $faculty_id)->where('semester_id', $semester_id);
            })->get();
        $genName = str_replace("-", "", $listArticle[0]->faculty_semester->semester->name . Carbon::now()->toDateString());
        $genName = str_replace(" ", "", $genName);
        $tempDir = storage_path("app/backups/faculty/" . $listArticle[0]->faculty_semester->id);
        $arrFile = 0;
        foreach ($listArticle as $article) {
            $arrFile = $arrFile + sizeof($article->article_file);
        }
        if ($arrFile == 0) {
            return false;
        }
        if (!file_exists($tempDir))
            File::makeDirectory($tempDir, 0777, true);
        $rawZipper = new ZipArchive();
        $tempDir = $tempDir . '/' . $genName . '.zip';
        $rawZipper->open($tempDir, ZipArchive::CREATE);
        if ($rawZipper != true) {
            return false;
        }
        foreach ($listArticle as $article) {
            foreach ($article->article_file as $file) {
                $dirFile = StorageHelper::locatePath(StorageHelper::getArticleFilePath($article->faculty_semester_id,
                    $article->id, $file->title));
                $rawZipper->addFile($dirFile, basename($dirFile));
            }
        }
        $rawZipper->close();
        return $tempDir;
    }
    public function downloadArticleSemester($semester_id)
    {
        $listArticle = Article::with('student')
            ->whereHas('faculty_semester', function (Builder $builder) use ($semester_id) {
                $builder->where('semester_id', $semester_id);
            })->get();
        if (sizeof($listArticle) > 0) {
            $genName = str_replace("-", "", $listArticle[0]->faculty_semester->semester->name . Carbon::now()->toDateString());
            $genName = str_replace(" ", "", $genName);
            $tempDir = storage_path("app/backups/semester/" . $listArticle[0]->faculty_semester->id);
            if (!file_exists($tempDir))
                File::makeDirectory($tempDir, 0777, true);
            $rawZipper = new ZipArchive();
            $tempDir = $tempDir . '/' . $genName . '.zip';
            $rawZipper->open($tempDir, ZipArchive::CREATE);
            if ($rawZipper != true) {
                return false;
            }
            foreach ($listArticle as $article) {
                foreach ($article->article_file as $file) {
                    $dirFile = StorageHelper::locatePath(StorageHelper::getArticleFilePath($article->faculty_semester_id,
                        $article->id, $file->title));
                    $rawZipper->addFile($dirFile,
                        'std' . $article->student_id . '-' .
                        $article->student->first_name . '-' . $article->student->last_name . '-' .
                        basename($dirFile));
                }
            }
            $rawZipper->close();
            return $tempDir;
        }
        return false;
    }
}
