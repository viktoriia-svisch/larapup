<?php
namespace App\Http\Controllers\Coordinator;
use App\Helpers\DateTimeHelper;
use App\Http\Controllers\FacultySemesterBaseController;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemester;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class SemesterController extends FacultySemesterBaseController
{
    public function listSemester(Request $request)
    {
        $searchTerms = $request->get('search');
        $master = Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER'];
        $currentSemester = Semester::with('faculty_semester')
            ->where('start_date', '<=', Carbon::now()->toDateTimeString())
            ->where('end_date', '>', Carbon::now()->toDateTimeString())
            ->first();
        $semestersFuture = Semester::with(['faculty_semester'])
            ->where('start_date', '>=', Carbon::now());
        $semestersPast = Semester::with(['faculty_semester'])
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
        if (!$master) {
            $currentSemester = $currentSemester
                ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $query) {
                    $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
                });
            $semestersFuture = $semestersFuture
                ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $query) {
                    $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
                });
            $semestersPast = $semestersPast
                ->whereHas('faculty_semester.faculty_semester_coordinator.coordinator', function (Builder $query) {
                    $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
                });
        }
        return view('coordinator.Semester.semester', [
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
        if (!$semester) return
            redirect()->route("coordinator.manageSemester")
                ->with($this->responseBladeMessage("Unable to find the semester", false));
        $listFaculty = FacultySemester::with("faculty")
            ->where("semester_id", $semester_id);
        if (Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['NORMAL']) {
            $listFaculty = $listFaculty->whereHas("faculty_semester_coordinator", function (Builder $builder) {
                $builder->where("coordinator_id", Auth::guard(COORDINATOR_GUARD)->user()->id);
            });
        }
        return view('coordinator.Semester.semester-detail')
            ->with([
                'semester' => $semester,
                'listFacultySemester' => $listFaculty->paginate(PER_PAGE)
            ]);
    }
    public function semesterDetail_removeFaculty(Request $request, $semester_id)
    {
        $facultyID = $request->get("faculty_id") ?? null;
        if (!$facultyID) {
            return redirect()->route("coordinator.semester.detail", [$semester_id])
                ->with($this->responseBladeMessage(
                    "Unable to get information of the faculty", false));
        }
        $facultySemester = $this->retrieveFacultySemester($facultyID, $semester_id, null);
        if (!$facultySemester) {
            return redirect()->route("coordinator.semester.detail", [$semester_id])
                ->with($this->responseBladeMessage(
                    "Unable to get information of the semester", false));
        }
        if (DateTimeHelper::isNowPassedDate($facultySemester->semester->start_date)) {
            return redirect()->back()
                ->with($this->responseBladeMessage(
                    "This semester was started, therefore cannot delete the existed information", false));
        }
        DB::beginTransaction();
        $deletingCoordinator = $facultySemester->faculty_semester_coordinator()->delete();
        $deletingStudent = $facultySemester->faculty_semester_student()->delete();
        if ($deletingCoordinator && $deletingStudent) {
            if ($facultySemester->delete()) {
                DB::commit();
                return back()->with(
                    $this->responseBladeMessage("Remove successfully")
                );
            }
        }
        DB::rollBack();
        return redirect()->back()
            ->with($this->responseBladeMessage(
                "Cannot delete the faculty. Please try again.", false)
            );
    }
    public function semesterDetail_addFaculty(Request $request, $semester_id)
    {
        $semester = Semester::with("faculty_semester")
            ->find($semester_id);
        $search = $request->get("search") ?? null;
        if (!$semester || DateTimeHelper::isNowPassedDate($semester->start_date)) {
            return redirect()->route('coordinator.semester.detail', [$semester_id])
                ->with("Unable to find the information, please try again.", false);
        }
        $freeFaculty = Faculty::with("faculty_semester")
            ->whereDoesntHave("faculty_semester", function (Builder $builder) use ($semester_id) {
                $builder->where("semester_id", $semester_id);
            })->get();
        $freeCoordinator = Coordinator::with('faculty_semester_coordinator')
            ->whereDoesntHave("faculty_semester_coordinator.faculty_semester", function (Builder $builder) use ($semester_id) {
                $builder->where("semester_id", $semester_id);
            });
        if ($search) {
            $freeCoordinator = $freeCoordinator->where(function (Builder $builder) use ($search) {
                $builder->where("first_name", 'like', "%$search%")
                    ->orWhere("last_name", 'like', "%$search%")
                    ->orWhere("email", 'like', "%$search%");
            });
        }
        return view("coordinator.Semester.semester-detail-addFaculty", [
            'semester' => $semester,
            'faculties' => $freeFaculty,
            'coordinators' => $freeCoordinator->paginate(PER_PAGE)
        ]);
    }
    public function semesterDetail_addFaculty_post(Request $request, $semester_id)
    {
        $this->validate($request, [
            'faculty_id' => 'required|exists:faculties,id'
        ], [
            'faculty_id.required' => 'Missing information of the faculty, please try again',
            'faculty_id.exists' => 'The chosen faculty does not exist!'
        ]);
        $faculty_id = $request->get("faculty_id") ?? null;
        $semester = Semester::with("faculty_semester")
            ->where('start_date', '>', Carbon::now())
            ->whereDoesntHave('faculty_semester', function (Builder $builder) use ($faculty_id) {
                $builder->where('faculty_id', $faculty_id);
            })->where("id", $semester_id)->first();
        if (!$semester) {
            return back()->with($this->responseBladeMessage("Information of the semester is invalid", false));
        }
        DB::beginTransaction();
        $facSemester = new FacultySemester([
            "faculty_id" => $faculty_id,
            'semester_id' => $semester_id,
            'first_deadline' => $request->get("first_deadline"),
            'second_deadline' => $request->get("second_deadline"),
            'description' => $request->get("description")
        ]);
        if ($facSemester->save()) {
            DB::commit();
            return redirect()->route("coordinator.semester.detail", [$semester_id])
                ->with($this->responseBladeMessage("Add new faculty to this semester successfully!"));
        }
        DB::rollback();
        return redirect()->route("coordinator.semester.detail", [$semester_id])
            ->with($this->responseBladeMessage("Unable to save new information!", false));
    }
}
