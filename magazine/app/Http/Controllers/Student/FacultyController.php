<?php
namespace App\Http\Controllers\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class FacultyController extends Controller
{
    public function faculty($id){
    	return view('student.faculty.faculty-detail');
	}
}
