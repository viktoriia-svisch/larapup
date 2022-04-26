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
        return view('admin.Semester.create-semester',[
            'lastSemester' => $lastSem
        ]);
    }
    public function createSemester_post(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $enddate = Carbon::parse($ad->start_date)->addMonth(3);
        $ad->end_date = $enddate;
        if ($ad->save())
            return redirect()->back()->with([
                'success' => 1
            ]);
        return redirect()->back()->with([
            'success' => 0
        ]);
    }
}
