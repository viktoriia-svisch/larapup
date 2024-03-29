<?php
namespace App\Http\Controllers\Coordinator;
use App\Helpers\StorageHelper;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Http\Requests\PublishRequest;
use App\Http\Requests\UpdateFacultySemester;
use App\Models\Article;
use App\Models\Coordinator;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\FacultySemesterStudent;
use App\Models\Publish;
use App\Models\PublishImage;
use App\Models\Student;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
    public function facultyDetailDashboard($faculty_id, $semester_id)
    {
        $facultySemester = FacultySemester::with("semester")
            ->where("semester_id", $semester_id)
            ->where("faculty_id", $faculty_id)
            ->first();
        if (!$facultySemester) {
            return redirect()->route("coordinator.dashboard");
        }
        $articleSubmission = $facultySemester->article()->paginate(PER_PAGE);
        $articleTotal = $facultySemester->article()->count("id");
        $studentTotal = $facultySemester->faculty_semester_student()->count();
        $average_grade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.id', '=', $facultySemester->id)
            ->avg('grade');
        $submissionOutOfDate = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '>', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.id', '=', $facultySemester->id)
            ->count();
        $submissionOnTime = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '<=', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.id', '=', $facultySemester->id)
            ->where("articles.status", '=', ARTICLE_STATUS["PUBLISHED"])
            ->count();
        $highestGrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.id', '=', $facultySemester->id)
            ->max('grade');
        $lowestGrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.id', '=', $facultySemester->id)
            ->min('grade');
        $arrData = [
            "submissions" => $articleSubmission,
            "submissions_total" => $articleTotal,
            "submissions_late" => $submissionOutOfDate,
            "submissions_onTime" => $submissionOnTime,
            "student_total" => $studentTotal,
            "grade_average" => $average_grade,
            "grade_highest" => $highestGrade,
            "grade_lowest" => $lowestGrade,
        ];
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.faculty-detail-dashboard',
            "dashboard",
            $arrData,
            COORDINATOR_GUARD);
    }
    public function facultyBackupsDownload($faculty_id, $semester_id)
    {
        $viewingFac = FacultySemester::with("semester")
            ->where("semester_id", $semester_id)
            ->where("faculty_id", $faculty_id)
            ->first();
        if (!$viewingFac) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
        }
        if ($viewingFac->article()->count() == 0) {
            return redirect()->route("coordinator.faculty.dashboard", [$faculty_id, $semester_id])
                ->with($this->responseBladeMessage("This semester does not have any data", false));
        }
        $dirDownload = $this->downloadArticleFaculty($faculty_id, $semester_id);
        if ($dirDownload) {
            ob_end_clean();
            $headers = array(
                "Content-Type: application/octet-stream",
                "Content-Description: File Transfer",
                "Content-Transfer-Encoding: Binary",
                "Content-Length: " . filesize($dirDownload),
                "Content-Disposition: attachment; filename=\"" . basename($dirDownload) . "\"",
            );
            return Response::download($dirDownload, basename($dirDownload), $headers);
        }
        return redirect()->back()->with($this->responseBladeMessage("Unable to create backup or the backup is empty data", false));
    }
    public function facultyDetailMember(Request $request, $faculty_id, $semester_id)
    {
        $arrData = $this->retrieveFacultySemesterMembers($request, $faculty_id, $semester_id);
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.faculty-detail-member',
            "members", $arrData,
            COORDINATOR_GUARD);
    }
    public function facultyDetailMember_manage(Request $request, $faculty_id, $semester_id)
    {
        $facultySemester = FacultySemester::with("faculty")
            ->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->first();
        if (!$facultySemester) {
            return redirect()->back()
                ->with($this->responseBladeMessage("Unable to access this faculty. The information does not exist"));
        }
        $search = $request->get("search") ?? null;
        $studentAvailable = Student::with("article")
            ->where("status", STUDENT_STATUS['ONGOING'])
            ->whereDoesntHave("faculty_semester_student", function (Builder $builder) use ($facultySemester) {
                $builder->where("faculty_semester_id", $facultySemester->id);
            });
        $studentUnAvailable = Student::with("article")
            ->whereHas("faculty_semester_student", function (Builder $builder) use ($facultySemester) {
                $builder->where("faculty_semester_id", $facultySemester->id);
            });
        if ($search) {
            $studentAvailable = $studentAvailable
                ->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where("first_name", "like", "%$search%")
                        ->orWhere("last_name", "like", "%$search%")
                        ->orWhere("email", "like", "%$search%");
                });
            $studentUnAvailable = $studentUnAvailable
                ->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where("first_name", "like", "%$search%")
                        ->orWhere("last_name", "like", "%$search%")
                        ->orWhere("email", "like", "%$search%");
                });
        }
        $arrData = [
            'facultySemester' => $facultySemester,
            'studentAvailable' => $studentAvailable->paginate(PER_PAGE, ["*"], "available"),
            'studentUnAvailable' => $studentUnAvailable->paginate(PER_PAGE, ["*"], "unavailable"),
            "search" => $search
        ];
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.faculty-detail-member-manage',
            "members", $arrData,
            COORDINATOR_GUARD);
    }
    public function facultyDetailMember_manage_add($faculty_id, $semester_id, $student_id)
    {
        $facultySemester = FacultySemester::with("faculty")
            ->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->first();
        if (!$facultySemester) {
            return redirect()->back()
                ->with($this->responseBladeMessage("Unable to access this faculty. The information does not exist"));
        }
        DB::beginTransaction();
        $semesterStudent = FacultySemesterStudent::with("faculty_semester")
            ->firstOrNew([
                "faculty_semester_id" => $facultySemester->id,
                "student_id" => $student_id
            ]);
        if ($semesterStudent->save()) {
            DB::commit();
            return redirect(route("coordinator.faculty.students.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
                ->with($this->responseBladeMessage("Added student successfully", true));
        }
        DB::rollback();
        return redirect(route("coordinator.faculty.students.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
            ->with($this->responseBladeMessage("Unable to add this student, please re-check", false));
    }
    public function facultyDetailMember_manage_remove($faculty_id, $semester_id, $student_id)
    {
        $facultySemester = FacultySemester::with("faculty")
            ->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->first();
        if (!$facultySemester) {
            return redirect(route('coordinator.dashboard', [$facultySemester->faculty_id, $facultySemester->semester_id]))
                ->with($this->responseBladeMessage("Unable to access this faculty. The information does not exist"));
        }
        $semesterStudent = FacultySemesterStudent::with("faculty_semester")
            ->where("faculty_semester_id", $facultySemester->id)
            ->where("student_id", $student_id)
            ->first();
        DB::beginTransaction();
        if ($semesterStudent && $semesterStudent->delete()) {
            DB::commit();
            return redirect(route("coordinator.faculty.students.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
                ->with($this->responseBladeMessage("Remove the student successfully", true));
        }
        DB::rollback();
        return redirect(route("coordinator.faculty.students.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
            ->with($this->responseBladeMessage("Unable to remove the student, please try again", false));
    }
    public function facultyDetailMemberCoordinator_manage(Request $request, $faculty_id, $semester_id)
    {
        $facultySemester = FacultySemester::with("faculty")
            ->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->first();
        if (!$facultySemester) {
            return redirect()->back()
                ->with($this->responseBladeMessage("Unable to access this faculty. The information does not exist"));
        }
        $search = $request->get("search") ?? null;
        $coordinatorAvailable = Coordinator::with("faculty_semester_coordinator")
            ->where("status", COORDINATOR_STATUS['ACTIVE'])
            ->whereDoesntHave("faculty_semester_coordinator", function (Builder $builder) use ($facultySemester) {
                $builder->where("faculty_semester_id", $facultySemester->id);
            });
        $coordinatorUnAvailable = Coordinator::with("faculty_semester_coordinator")
            ->whereHas("faculty_semester_coordinator", function (Builder $builder) use ($facultySemester) {
                $builder->where("faculty_semester_id", $facultySemester->id);
            });
        if ($search) {
            $coordinatorAvailable = $coordinatorAvailable
                ->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where("first_name", "like", "%$search%")
                        ->orWhere("last_name", "like", "%$search%")
                        ->orWhere("email", "like", "%$search%");
                });
            $coordinatorUnAvailable = $coordinatorUnAvailable
                ->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where("first_name", "like", "%$search%")
                        ->orWhere("last_name", "like", "%$search%")
                        ->orWhere("email", "like", "%$search%");
                });
        }
        $arrData = [
            'facultySemester' => $facultySemester,
            'coordinatorAvailable' => $coordinatorAvailable->paginate(PER_PAGE, ["*"], "available"),
            'coordinatorUnAvailable' => $coordinatorUnAvailable->paginate(PER_PAGE, ["*"], "unavailable"),
            "search" => $search
        ];
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.faculty-detail-coordinator-manage',
            "members", $arrData,
            COORDINATOR_GUARD);
    }
    public function facultyDetailMemberCoordinator_manage_add($faculty_id, $semester_id, $coordinator_id)
    {
        $facultySemester = FacultySemester::with("faculty")
            ->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->first();
        if (!$facultySemester) {
            return redirect()->back()
                ->with($this->responseBladeMessage("Unable to access this faculty. The information does not exist"));
        }
        DB::beginTransaction();
        $semesterCoordinator = FacultySemesterCoordinator::with("faculty_semester")
            ->firstOrNew([
                "faculty_semester_id" => $facultySemester->id,
                "coordinator_id" => $coordinator_id
            ]);
        if ($semesterCoordinator->save()) {
            DB::commit();
            return redirect(route("coordinator.faculty.coordinators.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
                ->with($this->responseBladeMessage("Added coordinator successfully", true));
        }
        DB::rollback();
        return redirect(route("coordinator.faculty.coordinators.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
            ->with($this->responseBladeMessage("Unable to add this coordinator, please re-check", false));
    }
    public function facultyDetailMemberCoordinator_manage_remove($faculty_id, $semester_id, $coordinator_id)
    {
        $facultySemester = FacultySemester::with("faculty")
            ->where("faculty_id", $faculty_id)
            ->where("semester_id", $semester_id)
            ->first();
        if (!$facultySemester) {
            return redirect(route('coordinator.dashboard', [$facultySemester->faculty_id, $facultySemester->semester_id]))
                ->with($this->responseBladeMessage("Unable to access this faculty. The information does not exist"));
        }
        $semesterCoordinator = FacultySemesterCoordinator::with("faculty_semester")
            ->where("faculty_semester_id", $facultySemester->id)
            ->where("coordinator_id", $coordinator_id)
            ->first();
        DB::beginTransaction();
        if ($semesterCoordinator && $semesterCoordinator->delete()) {
            DB::commit();
            return redirect(route("coordinator.faculty.coordinators.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
                ->with($this->responseBladeMessage("Remove the coordinator successfully", true));
        }
        DB::rollback();
        return redirect(route("coordinator.faculty.coordinators.manage", [$facultySemester->faculty_id, $facultySemester->semester_id]))
            ->with($this->responseBladeMessage("Unable to remove the coordinator, please try again", false));
    }
    public function facultyDetailSettings($faculty_id, $semester_id)
    {
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.faculty-detail-settings',
            "settings", [],
            COORDINATOR_GUARD);
    }
    public function facultyDetailSettingPost(UpdateFacultySemester $request, $faculty_id, $semester_id)
    {
        $facultyUpdate = FacultySemester::Where('semester_id', $semester_id)->Where('faculty_id', $faculty_id)->first();
        $facultyUpdate->first_deadline = Carbon::parse($request->get('first_deadline')) ?? $facultyUpdate->first_deadline;
        $facultyUpdate->second_deadline = Carbon::parse($request->get('second_deadline')) ?? $facultyUpdate->second_deadline;
        $facultyUpdate->description = $request->get('description') ?? $facultyUpdate->description;
        if ($facultyUpdate->save()) {
            return back()->with($this->responseBladeMessage('Update faculty success'));
        };
        return redirect()->back()->withInput();
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
        return $this->facultyDetail(
            $faculty_id,
            $semester_id,
            'coordinator.Faculty.Articles.faculty-detail-listArticle',
            "articles",
            [
                "articles" => $articles
            ]
        );
    }
    public function facultyDetailArticlePublish($faculty_id, $semester_id, $article_id)
    {
        $facultySemester = $this->retrieveFacultySemester($faculty_id, $semester_id);
        $article = $this->retrieveDetailArticle($faculty_id, $semester_id, $article_id);
        $publishing = Publish::with("publish_image")
            ->where("coordinator_id", Auth::guard(COORDINATOR_GUARD)->user()->id)
            ->where("article_id", $article_id)
            ->whereHas("article.faculty_semester", function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where("semester_id", $semester_id)
                    ->where("faculty_id", $faculty_id);
            })->first();
        if ($article && $facultySemester)
            return view("coordinator.Faculty.Articles.faculty-detail-publishing", [
                "facultySemester" => $facultySemester,
                "article" => $article,
                "published" => $publishing
            ]);
        return redirect()->route("coordinator.faculty.listArticle");
    }
    public function facultyDetailArticlePublish_Post(PublishRequest $request, $faculty_id, $semester_id, $article_id)
    {
        $title = $request->get("title");
        $listDescription = $request->get("description");
        $grade = $request->get("grade");
        $listImage = $request->get("old_image");
        $listNewImage = $request->file("image");
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
        $publishData = Publish::with("publish_image")->firstOrNew([
            "coordinator_id" => Auth::guard(COORDINATOR_GUARD)->user()->id,
            "article_id" => $article_id
        ]);
        $publishData->title = $title;
        $publishData->content = $listDescription;
        if (!$publishData->save()) {
            DB::rollback();
            return back()->with($this->responseBladeMessage("Cannot begin to publish", false));
        }
        $article->grade = $grade;
        $article->status = ARTICLE_STATUS["PUBLISHED"];
        if (!$article->save()) {
            DB::rollback();
            return back()->with($this->responseBladeMessage("Cannot begin to publish", false));
        }
        $arrExistedImage = [];
        foreach ($publishData->publish_image as $imgOb) {
            array_push($arrExistedImage, $imgOb->image_path);
        }
        if ($listImage)
            foreach ($listImage as $oldImageValidation) {
                if (!in_array($oldImageValidation, $arrExistedImage)) {
                    DB::rollBack();
                    return back()->with($this->responseBladeMessage("Invalid Integrity data!", false));
                }
            }
        $arrDeletedImage = [];
        if ($publishData->publish_image)
            foreach ($publishData->publish_image as $key => $image) {
                if (!in_array($image->image_path, $listImage)) {
                    if ($image->delete()) {
                        array_push($arrDeletedImage, $image);
                    } else {
                        DB::rollBack();
                        return back()->with($this->responseBladeMessage("Cannot delete old data", false));
                    }
                }
            }
        $arrNewImage = [];
        if ($listNewImage)
            foreach ($listNewImage as $key => $img) {
                try {
                    $newImage = new PublishImage([
                        "image_path" => StorageHelper::savePublishFileSubmission($facultySemester->id, $publishData->id, $img)["file"],
                        "image_ext" => FILE_EXT_INDEX[strtolower($img->getClientOriginalExtension())],
                        "description" => "N/D"
                    ]);
                    array_push($arrNewImage, $newImage);
                } catch (Exception $exception) {
                    DB::rollBack();
                    return back()->with($this->responseBladeMessage("Cannot save new data", false));
                }
            }
        if (sizeof($arrNewImage) > 0) {
            $resultSaved = $publishData->publish_image()->saveMany($arrNewImage);
            if (sizeof($resultSaved) > 0) {
                try {
                    foreach ($arrDeletedImage as $file) {
                        StorageHelper::deletePublishFile($facultySemester->id, $publishData->id, $file->image_path);
                    }
                    DB::commit();
                    return redirect()->back()->with($this->responseBladeMessage("Success"));
                } catch (Exception $exception) {
                    DB::commit();
                    return redirect()->back()->with($this->responseBladeMessage("Success but having some error in deleting physically"));
                }
            }
            DB::rollBack();
            return back()->with($this->responseBladeMessage("Cannot save new data", false));
        }
        DB::commit();
        return redirect()->back()->with($this->responseBladeMessage("Action Succeed"));
    }
}
