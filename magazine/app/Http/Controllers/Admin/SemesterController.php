<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class SemesterController extends Controller
{
    public function semester(Request $request)
    {
        $searchTerms = $request->get('search_semester_input');
        if ($searchTerms) {
            $semestersActive = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator'])
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->first();
            $semestersFuture = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator'])
                ->where('start_date', '>=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->orderBy('start_date', 'desc')
                ->get();
            $semestersPast = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator'])
                ->where('end_date', '<=', Carbon::now())
                ->where(function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                })
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $semestersActive = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator'])
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->first();
            $semestersFuture = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator'])
                ->where('start_date', '>=', Carbon::now())
                ->orderBy('start_date', 'desc')
                ->get();
            $semestersPast = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator'])
                ->where('end_date', '<=', Carbon::now())
                ->orderBy('start_date', 'desc')
                ->get();
        }
        return view('admin.Semester.semester', [
            'activeSemester' => $semestersActive,
            'futureSemester' => $semestersFuture,
            'pastSemester' => $semestersPast,
        ]);
    }
    public function semesterFind(Request $request)
    {
        return redirect()->back();
    }
    public function createSemester()
    {
        return view('admin.Semester.create-semester');
    }
    public function createSemester_post(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $ad->end_date = $request->get('end_date');
        if ($ad->save())
            return redirect()->back()->with([
                'success' => 1
            ]);
        return redirect()->back()->with([
            'success' => 0
        ]);
    }
}
