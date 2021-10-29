<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
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
    public function dashboard(){
        return view('coordinator.dashboard');
    }
    public function show($id)
    {
        $coordinator = Coordinator::find($id);
        return new CoordinatorResource($coordinator);
    }
}
