<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class SemesterController extends Controller
{
    public function semester()
    {
        $semesters = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator']);
        return view('admin.Semester.semester', [
            'listSemester' => $semesters
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
