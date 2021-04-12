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
    public function dashboard(){
        return view('student.dashboard');
    }
    public function store(CreateStudent $request)
    {
        $std = new Student();
        $std->email = $request->get('email');
        $std->password = $request->get('password');
        $std->first_name = $request->get('first_name');
        $std->last_name = $request->get('last_name');
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
    public function show($id)
    {
        $student = Student::find($id);
        return new StudentResource($student);
    }
}
