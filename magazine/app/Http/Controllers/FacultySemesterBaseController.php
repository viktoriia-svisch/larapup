<?php
namespace App\Http\Controllers;
use App\Helpers\StorageHelper;
use App\Models\Article;
use App\Models\CommentCoordinator;
use App\Models\CommentStudent;
use App\Models\FacultySemester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
    public function retrieveCommentAll($faculty_id, $semester_id, $guardStudent = STUDENT_GUARD, $guardCoordinator = COORDINATOR_GUARD)
    {
        $commentStudent = $this->retrieveCommentStudent($faculty_id, $semester_id, $guardStudent);
        $commentCoordinator = $this->retrieveCommentCoordinator($faculty_id, $semester_id, $guardCoordinator);
        $arrCombined = $commentStudent->concat($commentCoordinator)->sortByDesc(function ($comment) {
            return $comment['created_at'];
        });
        return $arrCombined;
    }
    public function retrieveCommentStudent($faculty_id, $semester_id, $guard = STUDENT_GUARD)
    {
        $commentStudent = CommentStudent::with("student")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        if ($guard == STUDENT_GUARD) {
            $commentStudent = $commentStudent->where("student_id", Auth::guard($guard)->user()->id);
        }
        return $commentStudent->get();
    }
    public function retrieveCommentCoordinator($faculty_id, $semester_id, $guard = COORDINATOR_GUARD)
    {
        $commentCoordinator = CommentCoordinator::with("coordinator")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            });
        if ($guard == COORDINATOR_GUARD) {
            $commentCoordinator = $commentCoordinator->where("coordinator_id", Auth::guard($guard)->user()->id);
        }
        return $commentCoordinator->get();
    }
    public function downloadArticleFaculty($faculty_id, $semester_id)
    {
        $listArticle = Article::with('student')
            ->whereHas('faculty_semester', function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where('faculty_id', $faculty_id)->where('semester_id', $semester_id);
            })->get();
        $tempDir = StorageHelper::getTemporaryBackupPath($faculty_id, $semester_id, 'backups.zip');
        $rawZipper = new ZipArchive();
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
}
