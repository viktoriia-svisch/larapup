<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
class SemesterController extends Controller
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
    public function chooseSemester($activeSemester)
    {
        $activeSemester = Semester::find($activeSemester);
        $static_info = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->join('faculties', 'faculty_semesters.faculty_id', '=', 'faculties.id')
            ->join('students', 'articles.student_id', '=', 'students.id')
            ->select('articles.grade', 'faculties.name as faculties_name', 'students.last_name as students_lname','articles.status',
                'articles.created_at', 'students.first_name as students_fname')
            ->where('faculty_semesters.semester_id', '=', $activeSemester->id)
            ->get();
        $student_count = DB::table('articles')->distinct('student_id')->count('student_id');
        $grade_avg = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $activeSemester->id)
            ->avg('grade');
        $outOfDate = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '>', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.semester_id', '=', $activeSemester->id)
            ->count();
        $inTime = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->whereColumn('articles.created_at', '<=', 'faculty_semesters.first_deadline')
            ->where('faculty_semesters.semester_id', '=', $activeSemester->id)
            ->count();
        $maxgrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $activeSemester->id)
            ->max('grade');
        $mingrade = DB::table('articles')
            ->join('faculty_semesters', 'articles.faculty_semester_id', '=', 'faculty_semesters.id')
            ->where('faculty_semesters.semester_id', '=', $activeSemester->id)
            ->min('grade');
        return view('admin.Semester.static-information')
            ->with([
                'activeSemester' => $activeSemester,
                'info' => $static_info,
                'countstudent' => $student_count,
                'grade_avg' => $grade_avg,
                'mingrade' => $mingrade,
                'maxgrade' => $maxgrade,
                'outOfDate' => $outOfDate,
                'inTime'=>$inTime
            ]);
    }
}
