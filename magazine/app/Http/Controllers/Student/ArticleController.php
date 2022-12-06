<?php
namespace App\Http\Controllers\Student;
use App\Helpers\StorageHelper;
use App\Helpers\UploadFileValidate;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Http\Requests\UploadArticleRequest;
use App\Models\Article;
use App\Models\ArticleFile;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class ArticleController extends Controller
{
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
                    $filePath = StorageHelper::saveArticleFileSubmission($article->faculty_semester_id, $article->id, $file);
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
                $fileEXT = UploadFileValidate::checkExtension($file->getClientOriginalExtension());
                $articleFile->type = FILE_EXT_INDEX[$fileEXT];
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
    public function articleFileDownload($faculty_id, $semester_id, $article_file_id)
    {
        $articleFile = ArticleFile::with("article")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            })->whereHas("article.student", function (Builder $query) {
                $query->where("id", Auth::guard(STUDENT_GUARD)->id());
            })
            ->where("id", $article_file_id)
            ->first();
        if ($articleFile) {
            $path = StorageHelper::locatePath(StorageHelper::getArticleFilePath($articleFile->article_id, $articleFile->title));
            return Response::download($path, $articleFile->title);
        }
        return redirect()->back()->with($this->responseBladeMessage("Failed to retrieve the file", false));
    }
    public function articleFileDelete(Request $request, $faculty_id, $semester_id)
    {
        $article_file_id = $request->get("survey_file_id");
        $articleFile = ArticleFile::with("article")
            ->whereHas("article.faculty_semester", function (Builder $query) use ($faculty_id, $semester_id) {
                $query->where("faculty_id", $faculty_id)->where("semester_id", $semester_id);
            })->whereHas("article.student", function (Builder $query) {
                $query->where("id", Auth::guard(STUDENT_GUARD)->id());
            })
            ->where("id", $article_file_id)
            ->first();
        if ($articleFile) {
            DB::beginTransaction();
            if ($articleFile->delete()) {
                $deleteStatus = StorageHelper::deleteArticleFile(
                    $articleFile->article->faculty_semester_id,
                    $articleFile->article_id,
                    $articleFile->title
                );
                if ($deleteStatus) {
                    DB::commit();
                    return redirect()->back()->with($this->responseBladeMessage("You have deleted the submission."));
                }
                DB::rollback();
                return redirect()->back()->with($this->responseBladeMessage("Failed to delete the file. Cannot locate the file in the system", false));
            }
            DB::rollback();
            return redirect()->back()->with($this->responseBladeMessage("Failed to delete the file. Cannot remove from database", false));
        }
        return redirect()->back()->with($this->responseBladeMessage("Failed to retrieve the file. This file might not belongs to yours", false));
    }
}