<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFaculty;
use App\Models\Faculty;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Http\Request;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $searchTerms = $request->get('search_faculty_input');
        if ($searchTerms) {
            $faculties = Faculty::with('faculty_semester')
                ->where('name', 'LIKE', '%' . $searchTerms . '%')
                ->paginate(PER_PAGE);
            return view('admin.faculty.faculty', [
                'faculties' => $faculties,
                'searching' => $searchTerms
            ]);
        }
        $faculties = Faculty::with('faculty_semester')
            ->paginate(PER_PAGE);
        return view('admin.faculty.faculty', [
            'faculties' => $faculties,
            'searching' => false
        ]);
    }
    public function createFaculty_semester()
    {
        $listSemester = Semester::
        where('start_date', ">", Carbon::now()->toDateTimeString())
            ->orderBy('start_date', 'asc')
            ->get();
        return view('admin.faculty.choose-semester', ['availableSemester' => $listSemester]);
    }
    public function createFaculty($semester)
    {
        $retrievedSemester = Semester::find($semester);
        return view('admin.faculty.create-faculty', ['semester' => $retrievedSemester]);
    }
    public function createFaculty_post($semester, CreateFaculty $request)
    {
        $coor = new Faculty;
        $coor->semester_id = $semester->id;
        $coor->name = $request->input('name');
        $coor->first_deadline = $request->input('first_deadline');
        $coor->second_deadline = $request->input('second_deadline');
        if ($coor->save()) {
            return redirect()->back()->with([
                'success' => true
            ]);
        }
        return redirect()->back()->with([
            'success' => false
        ]);
        return view('admin.faculty.create-faculty');
    }
    public function searchFaculty($semester, $request)
    {
        $search = Faculty::where('name', 'LIKE', '%' . $request . '%')
            ->where('semester_id', 'like', '%' . $semester . '%')
            ->get();
        return response()->json($search);
    }
}
