<?php
namespace App\Http\Controllers\Admin;
use App\Helpers\DateTimeHelper;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Http\Requests\CreateSemester;
use App\Http\Requests\UpdateSemester;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\Semester;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class SemesterController extends FacultySemesterBaseController
{
    public function semester(Request $request)
    {
        $searchTerms = $request->get('search_semester_input');
        $semestersActive = Semester::with(['faculty_semester'])
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
        $semestersFuture = Semester::with(['faculty_semester'])
            ->where('start_date', '>=', Carbon::now());
        $semestersPast = Semester::with(['faculty_semester'])
            ->where('end_date', '<=', Carbon::now());
        if ($searchTerms) {
            $semestersFuture = $semestersFuture
                ->where(function (Builder $query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                });
            $semestersPast = $semestersPast
                ->where(function (Builder $query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                });
        }
        return view('admin.Semester.semester', [
            'activeSemester' => $semestersActive,
            'futureSemester' => $semestersFuture->orderBy('start_date', 'desc')->get(),
            'pastSemester' => $semestersPast->orderBy('start_date', 'desc')->get(),
            'searching' => $searchTerms
        ]);
    }
    public function semester_faculties(Request $request, $semester_id)
    {
        $search = $request->get("search") ?? null;
        $viewingSemester = $this->retrieveCurrentSemester(null, $semester_id);
        if (!$viewingSemester) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
        }
        $faculties = FacultySemester::with("semester")
            ->where("semester_id", $semester_id);
        $facultyList = Faculty::with('faculty_semester')
            ->WhereDoesntHave('faculty_semester', function (Builder $builder) use ($semester_id) {
                $builder->where("semester_id", $semester_id);
            });
        if ($search) {
            $faculties = $faculties
                ->whereHas('faculty', function (Builder $builder) use ($search) {
                    $builder->where("name", "like", "%$search%");
                });
            $facultyList = $facultyList->where("name", "like", "%$search%");
        }
        return view('admin.Semester.semester-faculties')
            ->with([
                'currentSemester' => $viewingSemester,
                'facultyList' => $facultyList->paginate(PER_PAGE),
                "faculties" => $faculties->get(),
                'search' => $search
            ]);
    }
    public function createSemester()
    {
        $lastSem = Semester::with("faculty_semester")->orderBy('end_date', 'desc')->first();
        return view('admin.Semester.create-semester', [
            'lastSemester' => $lastSem
        ]);
    }
    public function createSemester_post(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $endDate = Carbon::parse($ad->start_date)->addMonth(3);
        $ad->end_date = $endDate;
        if ($ad->save())
            return back()->with(
                $this->responseBladeMessage('Create semester successfully.')
            );
        return back()->with(
            $this->responseBladeMessage('Create semester unsuccessfully. Please try again', false)
        );
    }
    public function createSemesterFaculty()
    {
        $lastSem = Semester::with("faculty_semester")->orderBy('end_date', 'desc')->first();
        return view('admin.Semester.create-semester', [
            'lastSemester' => $lastSem
        ]);
    }
    public function createSemesterFaculty_post()
    {
        $lastSem = Semester::with("faculty_semester")->orderBy('end_date', 'desc')->first();
        return view('admin.Semester.create-semester', [
            'lastSemester' => $lastSem
        ]);
    }
    public function semesterStatistic($semester_id)
    {
        $viewingSemester = $this->retrieveCurrentSemester(null, $semester_id);
        if (!$viewingSemester) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
        }
        $articleInSemester = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->join('faculties', 'faculty_semesters.faculty_id', '=', 'faculties.id')
            ->join('students', 'articles.student_id', '=', 'students.id')
            ->select('articles.grade', 'faculties.name as faculties_name', 'students.last_name as students_lname', 'articles.status',
                'articles.created_at', 'students.first_name as students_fname')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)
            ->paginate(PER_PAGE);
        $student_count = DB::table('articles')->distinct('student_id')->count('student_id');
        $grade_avg = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)
            ->avg('grade');
        $outOfDate = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '>', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)->count();
        $inTime = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '<=', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)->count();
        $maxgrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)->max('grade');
        $mingrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)->min('grade');
        return view('admin.Semester.semester-statistic')
            ->with([
                'currentSemester' => $viewingSemester,
                'info' => $articleInSemester,
                'countstudent' => $student_count,
                'grade_avg' => $grade_avg,
                'mingrade' => $mingrade,
                'maxgrade' => $maxgrade,
                'outOfDate' => $outOfDate,
                'inTime' => $inTime
            ]);
    }
    public function downloadBackups($semester_id)
    {
        $viewingSemester = $this->retrieveCurrentSemester(null, $semester_id);
        if (!$viewingSemester) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
        }
        $arrFile = [];
        foreach ($viewingSemester->faculty_semester as $facSemester) {
            foreach ($facSemester->article as $article) {
                $arrFile = array_merge($arrFile, $article->article_file->toArray());
            }
        }
        if (sizeof($arrFile) == 0) {
            return redirect()->route("admin.infoSemester", [$semester_id])
                ->with($this->responseBladeMessage("This semester does not have any data", false));
        }
        $dirDownload = $this->downloadArticleSemester($semester_id);
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
        return redirect()->back()->with($this->responseBladeMessage("Unable to create backup", true));
    }
    public function updateSemester($semester_id)
    {
        $viewingSemester = $this->retrieveCurrentSemester(null, $semester_id);
        if (!$viewingSemester) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
        }
        return view('admin.Semester.semester-settings')
            ->with([
                'currentSemester' => $viewingSemester
            ]);
    }
    public function updateSemesterPost(UpdateSemester $request, $id)
    {
        $viewingSemester = $this->retrieveCurrentSemester(null, $id);
        if (DateTimeHelper::isNowPassedDate($viewingSemester->start_date)) {
            return back()
                ->withInput()
                ->with($this->responseBladeMessage("The semester was already activated. Changes cannot be applied", false));
        }
        if (!$viewingSemester) return redirect()
            ->back()
            ->withInput()
            ->with($this->responseBladeMessage("Unable to find the correct semester!", false));
        if (!DateTimeHelper::isNowPassedDate($viewingSemester->start_date)) {
            $viewingSemester->name = $request->get('name') ?? $viewingSemester->name;
            $viewingSemester->start_date = $request->get('start_date') ?? $viewingSemester->start_date;
            $viewingSemester->end_date = $request->get('end_date') ?? $viewingSemester->end_date;
        }
        if (!DateTimeHelper::isNowPassedDate($viewingSemester->end_date)) {
            $viewingSemester->description = $request->get('description') ?? $viewingSemester->description;
        }
        if ($viewingSemester->save()) {
            return back()->with($this->responseBladeMessage("Update successfully!"));
        }
        return back()->with($this->responseBladeMessage("Update failed!", false));
    }
    public function deleteSemester($semester_id)
    {
        $viewingSemester = $this->retrieveCurrentSemester(null, $semester_id);
        if (DateTimeHelper::isNowPassedDate($viewingSemester->start_date)) {
            return back()
                ->withInput()
                ->with($this->responseBladeMessage("The semester was already activated. Changes cannot be applied", false));
        }
        if (!$viewingSemester) return redirect()
            ->back()
            ->withInput()
            ->with($this->responseBladeMessage("Unable to find the correct semester!", false));
        try {
            if ($viewingSemester->delete()) {
                return redirect()->route("admin.semester")->with($this->responseBladeMessage("Deleted the semester."));
            }
        } catch (Exception $e) {
        }
        return redirect()->back()->with($this->responseBladeMessage("Unable to delete this semester, please try again in a moment.", false));
    }
}
