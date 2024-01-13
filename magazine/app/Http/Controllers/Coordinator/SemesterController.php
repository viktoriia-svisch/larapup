<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\FacultySemester;
use App\Models\FacultySemesterCoordinator;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SemesterController extends FacultySemesterBaseController
{
    public function listSemester(Request $request)
    {
        $searchTerms = $request->get('search');
        $facultySemesterCoordinator = FacultySemesterCoordinator::with("faculty_semester")
            ->where('coordinator_id', Auth::guard(COORDINATOR_GUARD)->user()->id)
            ->whereHas("faculty_semester.semester")
            ->first();
        $currentSemester = Semester::with('faculty_semester')
            ->where('start_date', '<=', Carbon::now()->toDateTimeString())
            ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $query) {
                $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->where('end_date', '>', Carbon::now()->toDateTimeString())
            ->first();
        $semestersFuture = Semester::with(['faculty_semester'])
            ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $query) {
                $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->where('start_date', '>=', Carbon::now());
        $semestersPast = Semester::with(['faculty_semester'])
            ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $query) {
                $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->where('end_date', '<=', Carbon::now());
        if ($searchTerms) {
            $semestersFuture = $semestersFuture
                ->where(function (Builder $query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                });
            $semestersPast = $semestersPast
                ->where(function (Builder $query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms . '%')
                        ->orWhere('start_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('end_date', 'like', '%' . $searchTerms . '%')
                        ->orWhere('description', 'like', '%' . $searchTerms . '%');
                });
        }
        return view('coordinator.Semester.semester', [
                'facSemesterCoordinator' => $facultySemesterCoordinator,
                "activeSemester" => $currentSemester,
                'futureSemester' => $semestersFuture->orderBy('start_date', 'desc')->get(),
                'pastSemester' => $semestersPast->orderBy('start_date', 'desc')->get(),
                'search' => $searchTerms
            ]
        );
    }
    public function semesterDetail($semester_id)
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
        return view('coordinator.Semester.semester-detail')
            ->with([
                'semester' => $semester,
                'listFacultySemester' => $listFaculty
            ]);
    }
}
