<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudent;
use App\Http\Resources\Student as StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
class StudentController extends Controller
{
    public function index()
    {
        $students = Student::paginate(PER_PAGE);
        return StudentResource::collection($students);
    }
    public function article(){
        return view('shared.article');
    }
    public function dashboard(){
        return view('student.dashboard');
    }
    public function store(CreateStudent $request)
    {
        $std = new Student();
        $std->email = $request->get('email');
        $std->password = $request->get('password');
        $std->firstname = $request->get('first_name');
        $std->lastname = $request->get('last_name');
        $std->status = 1;
        if ($std->save())
            return $this->responseMessage(
                'New student created successfully',
                false,
                'success',
                $std
            );
        return $this->responseMessage('Create unsuccessfully', true);
    }
    public function search($request){
        $search = Student::where('first_name', 'LIKE', '%' . $request . '%')
            ->orWhere('last_name', 'like', '%' . $request . '%')
            ->get();
        return response()->json($search);
    }
    public function searchAll(Request $request){
        $data = $request->get('data');
        $search = Student::where('first_name', 'like', "%{$data}%")
            ->orWhere('last_name', 'like', "%{$data}%")
            ->get();
        return response()->json($search);
    }
    public function show($id)
    {
        $student = Student::find($id);
        return new StudentResource($student);
    }
}
