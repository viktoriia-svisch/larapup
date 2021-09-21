<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSemester;
use App\Semester;
use Illuminate\Http\Request;
use App\Http\Resources\Semester as SemesterResource;
use Carbon\Carbon;
class AdminController extends Controller
{
    public function index()
    {
        $sem = Semester::paginate((PER_PAGE));
        return SemesterResource::collection($sem);
    }
    public function create()
    {
    }
    public function store(CreateSemester $request)
    {
        $ad = new Semester();
        $ad->id = $request->get('id');
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
    public function show($id)
    {
    }
    public function edit($id)
    {
    }
    public function update(Request $request, $id)
    {
    }
    public function destroy($id)
    {
    }
}
