<?php
namespace App\Http\Controllers\Coordinator;
use App\Helpers\StorageHelper;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\ArticleFile;
use App\Models\CommentCoordinator;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public function commentPost(Request $request, $faculty_id, $semester_id, $article_id)
    {
        $facultySemester = $this->retrieveFacultySemester($faculty_id, $semester_id);
        $article = $this->retrieveDetailArticle($faculty_id, $semester_id, $article_id);
        $coordinator = Auth::guard(COORDINATOR_GUARD)->user();
        if (!$facultySemester || !$article) {
            return redirect()->route('coordinator.faculty.article')->with(
                $this->responseBladeMessage("Failed to retrieve data of current faculty and discussion", false)
            );
        }
        DB::beginTransaction();
        $comment = new CommentCoordinator([
            'article_id' => $article->id,
            'coordinator_id' => $coordinator->id,
            'content' => $request->get("content"),
        ]);
        if ($request->hasFile('attachment')) {
            $saveImageStatus = StorageHelper::saveCommentCoordinator(
                $coordinator->id, $article->id,
                $request->file("attachment"));
            if ($saveImageStatus) {
                $comment->image_path = $saveImageStatus;
                if ($comment->save()) {
                    DB::commit();
                    return back()->with(
                        $this->responseBladeMessage("You have commented")
                    );
                }
                StorageHelper::deleteCommentCoordinator($coordinator->id, $article->id, $saveImageStatus);
            }
            DB::rollback();
            return back()->with(
                $this->responseBladeMessage("Unable to save your comment. Try again later", false)
            );
        }
        if ($comment->save()) {
            DB::commit();
            return back()->with(
                $this->responseBladeMessage("Comment successfully")
            );
        }
        DB::rollback();
        return back()->with(
            $this->responseBladeMessage("Unable to save your comment. Try again later", false)
        );
    }
}
