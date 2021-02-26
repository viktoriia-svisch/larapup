<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCoordinator;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Models\Coordinator;
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
    public function store(CreateCoordinator $request)
    {
        $coor = new Coordinator();
        $coor->email = $request->get('email');
        $coor->password = $request->get('password');
        $coor->first_name = $request->get('first_name');
        $coor->last_name = $request->get('last_name');
        $coor->type = 1;
        $coor->status = 1;
        if ($coor->save())
            return $this->responseMessage(
                'New student created successfully',
                false,
                'success',
                $coor
            );
        return $this->responseMessage('Create unsuccessfully', true);
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
