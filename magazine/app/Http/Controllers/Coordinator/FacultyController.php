<?php
namespace App\Http\Controllers\Coordinator;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\FacultySemester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class FacultyController extends Controller
{
    public function faculty(Request $request)
    {
        $selectedMode = $request->get('viewMode');
        $searchTerms = $request->get('search_faculty_input');
        $listFaculty = FacultySemester::with(['faculty'])
            ->whereHas('faculty_semester_coordinator.coordinator', function ($query) {
                $query->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            });
        if ($selectedMode) {
            switch ($selectedMode) {
                case '1':
                    $listFaculty->whereHas('semester', function ($query) {
                        $query->whereDate('start_date', ">", Carbon::now()->toDateTimeString())
                            ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());;
                    });
                    break;
                case '2':
                    $listFaculty->whereHas('semester', function ($query) {
                        $query->whereDate('end_date', "<=", Carbon::now()->toDateTimeString())
                            ->whereDate('start_date', "<=", Carbon::now()->toDateTimeString());
                    });
                    break;
                default:
                    $selectedMode = '0';
            }
        } else {
            $selectedMode = '0';
        }
        if ($searchTerms) {
            $listFaculty->whereHas('faculty', function ($query) use ($searchTerms) {
                $query->where('name', 'like', '%' . $searchTerms . '%')
                    ->orWhereHas('faculty_semester.semester', function ($query) use ($searchTerms) {
                        $query->where('end_date', "like", '%' . $searchTerms . '%');
                    });
            });
        }
        $currentFaculty = FacultySemester::with(['faculty_semester_student.student'])
            ->whereHas('faculty_semester_coordinator.coordinator', function ($q) {
                $q->where('id', Auth::guard(COORDINATOR_GUARD)->user()->id);
            })
            ->whereHas('semester', function ($query) {
                $query->whereDate('start_date', "<", Carbon::now()->toDateTimeString())
                    ->whereDate('end_date', ">", Carbon::now()->toDateTimeString());
            })
            ->first();
        return view('coordinator.faculties', [
            'viewMode' => $selectedMode,
            'searchTerms' => $searchTerms,
            'semester_faculties' => $listFaculty
                ->orderBy('semester_id', 'desc')
                ->paginate(PER_PAGE),
            'currentFaculty' => $currentFaculty
        ]);
    }
}
