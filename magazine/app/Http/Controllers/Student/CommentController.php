<?php
namespace App\Http\Controllers\Student;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\CommentCoordinator;
use App\Models\CommentStudent;
use App\Models\FacultySemester;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class CommentController extends Controller
{
    public function commentPost(CommentRequest $request, $faculty_id, $semester_id)
    {
        $student = Auth::guard(STUDENT_GUARD)->user();
        $facSemester = FacultySemester::with("semester")->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->whereHas("faculty_semester_student", function (Builder $query) use ($student) {
                $query->where("student_id", $student->id);
            })
            ->whereHas("semester", function (Builder $query) {
                $query->whereDate("start_date", "<=", Carbon::now()->toDateTimeString())
                    ->whereDate("end_date", ">=", Carbon::now()->toDateTimeString());
            })->first();
        if ($facSemester) {
            $article = Article::with("student")
                ->firstOrCreate([
                    "student_id" => $student->id,
                    "faculty_semester_id" => $facSemester->id
                ]);
            DB::beginTransaction();
            $comment = new CommentStudent([
                'article_id' => $article->id,
                'student_id' => $student->id,
                'content' => $request->get("content"),
            ]);
            if ($request->hasFile('attachment')) {
                $saveImageStatus = StorageHelper::saveCommentStudent(
                    $student->id, $article->id,
                    $request->file("attachment"));
                if ($saveImageStatus) {
                    $comment->image_path = $saveImageStatus;
                    if ($comment->save()) {
                        DB::commit();
                        return back()->with(
                            $this->responseBladeMessage("Comment successfully")
                        );
                    }
                    StorageHelper::deleteCommentStudent($student->id, $article->id, $saveImageStatus);
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
        return back()->with($this->responseBladeMessage("You are not allowed to comment in this faculty", false));
    }
    public function downloadAttachment($faculty_id, $semester_id, $comment_id, $type)
    {
        if ($type == STUDENT_GUARD) {
            $comment = CommentStudent::with("article")
                ->where("id", $comment_id)
                ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                    $query->where("faculty_id", $faculty_id)
                        ->where("semester_id", $semester_id);
                })->where("student_id", Auth::id())->first();
            $pathRaw = StorageHelper::getCommentStudentPath(Auth::id(), $comment->article_id, $comment->image_path);
        } else {
            $comment = CommentCoordinator::with("article")
                ->where("id", $comment_id)
                ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                    $query->where("faculty_id", $faculty_id)
                        ->where("semester_id", $semester_id);
                })
                ->whereHas("article", function (Builder $query) use ($faculty_id, $semester_id) {
                    $query->where("student_id", Auth::id());
                })->first();
            $pathRaw = StorageHelper::getCommentCoordinatorPath($comment->coordinator_id, $comment->article_id, $comment->image_path);
        }
        if ($comment) {
            $path = StorageHelper::locatePath($pathRaw);
            return Response::download($path, $comment->image_path);
        }
        return back()->with($this->responseBladeMessage("Cannot find the file to download", false));
    }
}
