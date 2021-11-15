<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFaculty;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use Illuminate\Http\Request;
class CoordinatorController extends Controller
{
    public function index()
    {
        $coordinators = Coordinator::paginate(PER_PAGE);
        return CoordinatorResource::collection($coordinators);
    }
    public function create()
    {
    }
    public function store(Request $request)
    {
    }
    public function storeFaculty(CreateFaculty $request)
    {
        $coor = new Faculty();
        $coor->semester_id = $request->get('semester_id');
        $coor->name = $request->get('name');
        $coor->first_deadline = $request->get('first_deadline');
        $coor->second_deadline = $request->get('second_deadline');
        $checksemesterId = \DB::table('semesters')
        ->select('semesters.id')
            ->where('semesters.id', $coor->semester_id)
            ->first();
        $getSemesterDate = \DB::table('semesters')
        ->where('id', '=', $coor->semester_id)
            ->first();
        if (!$checksemesterId) 
        {
            return $this->responseMessage('There is no semester with id ' . $coor->semester_id . " .Please create one first", true);
        }
        if ($coor->first_deadline < $getSemesterDate->start_date || $coor->first_deadline > $getSemesterDate->end_date) 
        {
            return $this->responseMessage('Please enter the date between semester start and end date', true);
        }
        if ($coor->second_deadline < $getSemesterDate->start_date || $coor->second_deadline > $getSemesterDate->end_date)
        {
            return $this->responseMessage('Please enter the date between semester start and end date', true);
        } else if ($coor->save()) {
            return $this->responseMessage(
                'New faculty created successfully',
                false,
                'success',
                $coor
            );
        }
        return $this->responseMessage(
            'New faculty created unsuccessfully',
            true,
            'failed',
            $coor
        );
    }
    public function show($id)
    {
        $coordinator = Coordinator::find($id);
        return new CoordinatorResource($coordinator);
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
