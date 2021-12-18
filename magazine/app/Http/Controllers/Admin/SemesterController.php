<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Semester;
class SemesterController extends Controller
{
    public function semester()
    {
        $semesters = Semester::with(['faculty.faculty_student', 'faculty.faculty_coordinator']);
        return view('admin.Semester.semester', [
            'listSemester' => $semesters
        ]);
    }
    public function createSemester()
    {
        return view('admin.Semester.create-semester');
    }
}
