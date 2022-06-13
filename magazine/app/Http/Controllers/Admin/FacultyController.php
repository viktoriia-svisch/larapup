<?php
namespace App\Http\Controllers\Admin;
use App\Http\Requests\CreateFaculty;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Http\Request;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $listFaculty = Faculty::orderBy('name','asc')
            ->paginate(PER_PAGE);
        return view('admin.faculty.faculty')->with('listFaculty',$listFaculty);
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
    public function createFaculty_post(CreateFaculty $request)
    {
        $coor = new Faculty;
        $coor->name = $request->input('name');
        $validated = $request->validated();
        $DuplicateFaculty = Faculty::where('name','=',$request->name)->first();
        if(!empty($DuplicateFaculty))
          {
            return redirect()->back()->with([
                'success' => false]);
          }
        if ($coor->save())
            {
                return redirect()->back()->with([
                    'success' => true
                ]); 
            }
    }
    public function searchFaculty($semester, $request)
    {
        $search = Faculty::where('name', 'LIKE', '%' . $request . '%')
            ->where('semester_id', 'like', '%' . $semester . '%')
            ->get();
        return response()->json($search);
    }
}
