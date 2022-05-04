<?php
namespace App\Http\Controllers\Admin;
use App\Http\Requests\CreateCoordinator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class CoordinatorController extends Controller
{
    public function create()
    {
        return view('admin.coordinator.create-coordinator');
    }
    public function createCoordinator_post(CreateCoordinator $request){
        $student = new Student($request->all([
            'email',
            'password',
            'first_name',
            'last_name',
            'type',
            'status',
            'gender',
            'dateOfBirth'
        ]));
        if ($student->save())
            return back()->with($this->responseBladeMessage(__('message.create_coordinator_success')));
        return back()->with($this->responseBladeMessage(__('message.create_coordinator_failed'), false));
    }
}
