<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Coordinator;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
class CoordinatorController extends Controller
{
    public function coordinator(Request $request){
        $coordinators = Coordinator::where('first_name', 'LIKE', '%' . $request->get('search') . '%')
            ->orWhere('last_name', 'like', '%' . $request->get('search') . '%')
            ->paginate(1);
        return view('admin.Coordinator.coordinator', ['coordinators' => $coordinators]);
    }
}
