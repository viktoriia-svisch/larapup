<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
class SemesterController extends FacultySemesterBaseController
{
    public function listSemester()
    {
        $facultySemesterCoordinator = FacultySemesterCoordinator::with("faculty_semester")
            ->where('coordinator_id', Auth::guard(COORDINATOR_GUARD)->user()->id)
            ->whereHas("faculty_semester.semester")
            ->first();
        $currentSemester = Semester::with('faculty_semester')
            ->where('start_date', '<=', Carbon::now()->toDateTimeString())
            ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $q) {
                $q->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->where('end_date', '>', Carbon::now()->toDateTimeString())
            ->first();
        return view('coordinator.Semester.semester', [
                'facSemesterCoordinator' => $facultySemesterCoordinator,
                "currentSemester" => $currentSemester
            ]
        );
    }
    public function semesterFaculty($semester_id)
    {
        $semester = Semester::with("faculty_semester")->find($semester_id);
        if (!$semester) return redirect()
            ->route("coordinator.manageSemester")
            ->with($this->responseBladeMessage("Unable to find the semester", false));
        $listFaculty = FacultySemester::with("faculty")
            ->where("semester_id", $semester_id)
            ->whereHas("faculty_semester_coordinator", function (Builder $builder) {
                $builder->where("coordinator_id", Auth::guard(COORDINATOR_GUARD)->user()->id);
            })->paginate(PER_PAGE);
        return view('coordinator.Semester.choose-semester-faculty')
            ->with([
                'semester' => $semester,
                'listFacultySemester' => $listFaculty
            ]);
    }
}
