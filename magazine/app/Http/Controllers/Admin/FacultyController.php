<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
class FacultyController extends Controller
{
    public function faculty()
    {
        return view('admin.faculty.faculty');
    }
    public function createFaculty()
    {
        return view('admin.faculty.create-faculty');
    }
}
