<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Http\Request;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $listSemester = Semester::with('faculty_semester')
            ->where('start_date', ">", Carbon::now()->toDateTimeString())
            ->orderBy('start_date', 'asc')
            ->paginate(PER_PAGE);
        return view('admin.faculty.faculty', ['availableSemester' => $listSemester]);
    }
    public function createFaculty_semester()
    {
        $listSemester = Semester::with('faculty')
            ->where('start_date', ">", Carbon::now()->toDateTimeString())
            ->orderBy('start_date', 'asc')
            ->get();
        return view('admin.faculty.choose-semester', ['availableSemester' => $listSemester]);
    }
    public function createFaculty($semester)
    {
        $retrievedSemester = Semester::find($semester);
        return view('admin.faculty.create-faculty', ['semester' => $retrievedSemester]);
    }
    public function createFaculty_post($semester, Request $request)
    {
        dd($request);
        return view('admin.faculty.create-faculty');
    }
}
