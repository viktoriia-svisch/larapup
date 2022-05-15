<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Http\Requests\CreateFaculty;
use App\Models\Semester;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\FacultySemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        dd($sem);
    }
    public function store(Request $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $ad->end_date = $request->get('end_date');
        $validated = $request->validated();
        $OneYearLimit = Carbon::parse($ad->start_date)->addYear(); 
        if ($ad->end_date > $OneYearLimit)
        {
              return $this->responseMessage("The end date must not be more than 1 year from start date", true);
        }
         if ($ad->save())
        {
            return $this->responseMessage(
                'New semester created successfully',
                false,
                'success',
                $ad
            );
        }
        return $this->responseMessage('Create unsuccessfully', true);
        return view('admin.Semester.create-semester');
    }
    public function createFaculty(CreateFaculty $request)
    {
        $coor = new Faculty();
        $coor->semester_id= $request->get('id');
        $coor->name = $request->get('name');
        if ($coor->save())
            {
                return $this->responseMessage(
                    'New faculty created successfully',
                    false,
                    'success',
                    $coor
                );
            }
    }
    public function searchFaculty($semester, $request)
    {
        $search = FacultySemester::where('name', 'LIKE', '%' . $request . '%')
            ->where('semester_id', 'like', '%' . $semester . '%')
            ->get();
        return response()->json($search);
    }
    public function addStudentToFaculty()
    {
    }
    public function dashboard()
    {
        return view('admin.dashboard');
    }
}
