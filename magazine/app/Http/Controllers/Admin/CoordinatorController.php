<?php
namespace App\Http\Controllers\Admin;
use App\Http\Requests\CreateCoordinator;
use App\Models\Coordinator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class CoordinatorController extends Controller
{
    public function coordinator(){
        return view('admin.coordinator.coordinator');
    }
    public function create()
    {
        return view('admin.coordinator.create-coordinator');
    }
    public function createCoordinator_post(CreateCoordinator $request){
        $coordinator = new Coordinator($request->all([
            'email',
            'password',
            'first_name',
            'last_name',
            'gender',
            'dateOfBirth'
        ]));
        $coordinator->type = COORDINATOR_LEVEL['NORMAL'];
        if ($coordinator->save())
            return back()->with($this->responseBladeMessage(__('message.create_coordinator_success')));
        return back()->with($this->responseBladeMessage(__('message.create_coordinator_failed'), false));
    }
}
