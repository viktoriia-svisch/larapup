<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Http\Requests\CreateSemester;
use App\Http\Requests\UpdateSemester;
use App\Models\Semester;
use Exception;
use Illuminate\Contracts\View\Factory;
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
        $searching = false;
        if ($searchTerms) {
            $searching = $searchTerms;
            $semestersActive = Semester::with(['faculty_semester'])
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->first();
            $semestersFuture = Semester::with(['faculty_semester'])
                ->where('start_date', '>=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->orderBy('start_date', 'desc')
                ->get();
            $semestersPast = Semester::with(['faculty_semester'])
                ->where('end_date', '<=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $semestersActive = Semester::with(['faculty_semester'])
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->first();
            $semestersFuture = Semester::with(['faculty_semester'])
                ->where('start_date', '>=', Carbon::now())
                ->orderBy('start_date', 'desc')
                ->get();
            $semestersPast = Semester::with(['faculty_semester'])
                ->where('end_date', '<=', Carbon::now())
                ->orderBy('start_date', 'desc')
                ->get();
        }
        return view('admin.Semester.semester', [
            'activeSemester' => $semestersActive,
            'futureSemester' => $semestersFuture,
            'pastSemester' => $semestersPast,
            'searching' => $searching
        ]);
    }
    public function createSemester()
    {
        $lastSem = Semester::orderBy('end_date', 'desc')->first();
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
        $lastSem = Semester::orderBy('end_date', 'desc')->first();
        return view('admin.Semester.create-semester', [
            'lastSemester' => $lastSem
        ]);
    }
    public function createSemesterFaculty_post()
    {
        $lastSem = Semester::orderBy('end_date', 'desc')->first();
        return view('admin.Semester.create-semester', [
            'lastSemester' => $lastSem
        ]);
    }
    public function chooseSemester($semester_id)
    {
        $viewingSemester = Semester::with("faculty_semester")
            ->where("id", $semester_id)->first();
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
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)
            ->count();
        $inTime = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '<=', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)
            ->count();
        $maxgrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)
            ->max('grade');
        $mingrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $viewingSemester->id)
            ->min('grade');
        return view('admin.Semester.static-information')
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
        $viewingSemester = Semester::with("faculty_semester")
            ->where("id", $semester_id)->first();
        if (!$viewingSemester) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
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
    public function updateSemester($semester_id){
        $viewSemester = Semester::with("faculty_semester")
            ->where("id", $semester_id)->first();
        if (!$viewSemester) {
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the semester", false));
        }
        return view('admin.Semester.update-semester')
            ->with([
                'currentSemester' => $viewSemester
            ]);
    }
    public function updateSemesterPost(UpdateSemester $request, $id){
        $Semester = Semester::with("faculty_semester")->find($id);
        if (!$Semester) return redirect()->back()->withInput();
        $Semester->name = $request->get('name') ?? $Semester->name;
        $Semester->description = $request->get('description') ?? $Semester->description;
        $Semester->start_date = $request->get('start_date') ?? $Semester->start_date;
        $Semester->end_date = $request->get('end_date') ?? $Semester->end_date;
        if ($Semester->save()) {
            return back()->with([
                'updateStatus' => true
            ]);
        }
        return back()->with([
            'updateStatus' => false
        ]);
    }
}
