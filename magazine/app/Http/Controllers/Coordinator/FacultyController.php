<?php
namespace App\Http\Controllers\Coordinator;
use App\Helpers\StorageHelper;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\Article;
use App\Models\FacultySemester;
use App\Models\Publish;
use App\Models\PublishContent;
use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        return $this->facultyDetail($faculty_id, $semester_id, 'coordinator.Faculty.faculty-detail-article', "article");
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
    public function facultyDetailArticlePublish_Post(Request $request, $faculty_id, $semester_id, $article_id)
    {
        $title = $request->get("title");
        $listDescription = $request->get("description") ?? [];
        $listImage[] = $request->file("image") ?? [];
        $listImageDescription = $request->get("imageDescription") ?? [];
        if (sizeof($listDescription) !== sizeof($listImageDescription)) {
            return back()->with($this->responseBladeMessage("The sending data was not correct!", false));
        }
        $facultySemester = FacultySemester::with("semester")
            ->where("faculty_id", $faculty_id)->where("semester_id", $semester_id)
            ->whereHas("faculty_semester_coordinator", function (Builder $builder) {
                $builder->where("coordinator_id", Auth::guard(COORDINATOR_GUARD)->user()->id);
            })->whereHas("article", function (Builder $builder) use ($article_id) {
                $builder->where("id", $article_id);
            })->first();
        $article = $this->retrieveDetailArticle($faculty_id, $semester_id, $article_id);
        if (!$facultySemester || !$article) {
            return redirect()->route("coordinator.faculty.listArticle");
        }
        DB::beginTransaction();
        $publishData = Publish::with("publish_content")->firstOrNew([
            "coordinator_id" => Auth::guard(COORDINATOR_GUARD)->user()->id,
            "article_id" => $article_id
        ]);
        $publishData->title = $title;
        if (!$publishData->save()) {
            DB::rollback();
            return back()->with($this->responseBladeMessage("Cannot begin to publish", false));
        }
        $listPublishDataDetail = $publishData->publish_content;
        $arrNewContent = [];
        foreach ($listDescription as $key => $description) {
            try {
                $existedDetail = $listPublishDataDetail[$key];
                $existedDetail->content = $description;
                try {
                    $pathInfo = StorageHelper::savePublishFileSubmission($facultySemester->id, $publishData->id, $listImage[$key]);
                    if ($pathInfo && $pathInfo["file"]) {
                        $existedDetail->image_path = $pathInfo["file"];
                        $existedDetail->image_description = $listImageDescription[$key];
                    }
                } catch (ErrorException $exception) {
                } finally {
                    $existedDetail->publish_id = $publishData->id;
                    if (!$existedDetail->save()) {
                        DB::rollback();
                        return back()->with($this->responseBladeMessage("Cannot save the content of the publish document", false));
                    }
                }
            } catch (ErrorException $e) {
                $newContent = new PublishContent();
                $newContent->content = $description;
                try {
                    if ($listImage[$key]){
                        $pathInfo = StorageHelper::savePublishFileSubmission($facultySemester->id, $publishData->id, $listImage[$key]);
                        if ($pathInfo && $pathInfo["file"]) {
                            $newContent->image_path = $pathInfo["file"];
                            $newContent->image_description = $listImageDescription[$key];
                        }
                    }
                } catch (ErrorException $e) {
                } finally {
                    array_push($arrNewContent, $newContent);
                }
            }
        }
        if (sizeof($arrNewContent) > 0) {
            $models = $publishData->publish_content()->saveMany($arrNewContent);
            if (!$models || sizeof($models) == 0) {
                DB::rollback();
                return back()->with($this->responseBladeMessage("Cannot save the content of the publish document", false));
            }
        }
        if (sizeof($listDescription) < sizeof($listPublishDataDetail)) {
            $deletedCount = sizeof($listPublishDataDetail) - sizeof($listDescription);
        }
        if ($publishData->save()) {
            DB::commit();
            return back()->with($this->responseBladeMessage("Success", true));
        }
        DB::rollback();
        return back()->with($this->responseBladeMessage("Cannot save the content of the publish document", false));
    }
}
