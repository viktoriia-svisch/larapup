<?php
namespace App\Http\Controllers\Student;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CommentStudent;
use App\Models\FacultySemester;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class CommentController extends Controller
{
    public function commentPost(Request $request, $faculty_id, $semester_id)
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
}
