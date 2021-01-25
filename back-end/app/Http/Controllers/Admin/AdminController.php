<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Semester;
use Illuminate\Http\Request;
use App\Http\Resources\Semester as SemesterResource;
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
    public function store(Request $request)
    {
        $ad = new Semester();
        $ad->falcutyid = $request->get('falcutyid');
        $ad->name = $request->get('name');
        $ad->startdate = $request->get('startdate');
        $ad->enddate = $request->get('enddate');
        if ($ad->save())
            return $this->responseMessage(
                'New semester created successfully',
                false,
                'success',
                $ad
            );
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
