<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudent;
use App\Http\Resources\Student as StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        //
        $students = Student::paginate(PER_PAGE);
        return StudentResource::collection($students);

    }

    public function article(){
        return view('shared.article');
    }

    public function dashboard(){
        return view('student.dashboard');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateStudent $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return StudentResource
     */
    public function show($id)
    {
        $student = Student::find($id);
        return new StudentResource($student);
    }
}
