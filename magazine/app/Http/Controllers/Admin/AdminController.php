<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Models\Semester;
use App\Models\Student;
use App\Http\Resources\Semester as SemesterResource;
use Illuminate\Support\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        dd($sem);
    }
    public function store(Requestest $request)
    {
        $ad = new Semester();
        $ad->name = $request->get('name');
        $ad->description = $request->get('description');
        $ad->start_date = $request->get('start_date');
        $ad->end_date = $request->get('end_date');
        $validated = $request->validated();
        $OneYearLimit = Carbon::parse($ad->start_date)->addYear();
         if ($ad->end_date > $OneYearLimit){
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
    }
    public function dashboard()
    {
        return view('admin.dashboard');
    }
}
