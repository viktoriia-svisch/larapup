<?php
namespace App\Http\Controllers\Coordinator;
use App\Helpers\StorageHelper;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\ArticleFile;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class ArticleController extends FacultySemesterBaseController
{
    public function articleFileDownload($faculty_id, $semester_id, $article_file_id)
    {
        $articleFile = ArticleFile::with("article")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            })->whereHas("article.faculty_semester.faculty_semester_coordinator.coordinator", function (Builder $query) {
                $query->where("id", Auth::guard(COORDINATOR_GUARD)->id());
            })
            ->where("id", $article_file_id)
            ->first();
        if ($articleFile) {
            $path = StorageHelper::locatePath(StorageHelper::getArticleFilePath($articleFile->article->faculty_semester->id, $articleFile->article_id, $articleFile->title));
            return Response::download($path, $articleFile->title);
        }
        return redirect()->back()->with($this->responseBladeMessage("Failed to retrieve the file", false));
    }
    public function facultyArticleDiscussion($faculty_id, $semester_id, $article_id)
    {
        $article = $this->retrieveDetailArticle($faculty_id, $semester_id, $article_id);
        $listComment = $this->retrieveCommentAll($faculty_id, $semester_id, null, COORDINATOR_GUARD);
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.Articles.faculty-detail-discussion',
            "article",
            [
                "article" => $article,
                "comments" => $listComment
            ],
            COORDINATOR_GUARD);
    }
}
