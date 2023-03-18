<?php
namespace App\Http\Controllers;
use App\Models\Faculty;
use App\Models\Publish;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class GeneralController extends FacultySemesterBaseController
{
    public function published(Request $request, $faculty_id, $semester_id, $id_publish)
    {
        $retrievePublish = Publish::with("publish_content")
            ->where("id", $id_publish)
            ->whereHas("article.faculty_semester", function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where("semester_id", $semester_id)->where("faculty_id", $faculty_id);
            })->first();
        $faculty = Faculty::with('faculty_semester')
            ->whereHas('faculty_semester.semester', function (Builder $builder) use ($semester_id) {
                $builder->where("id", $semester_id);
            })
            ->find($faculty_id);
        if (!$faculty || !$retrievePublish) {
            return redirect()->route('student.login');
        }
        return view('shared.publish', [
            'publication' => $retrievePublish,
            'viewFaculty' => $faculty,
            'semester_id' => $semester_id
        ]);
    }
    public function listPublished(Request $request, $faculty_id, $semester_id)
    {
        if (Auth::guard(COORDINATOR_GUARD)->check()) {
            $guard = COORDINATOR_GUARD;
        } elseif (Auth::guard(STUDENT_GUARD)->check()) {
            $guard = STUDENT_GUARD;
        } elseif (Auth::guard(GUEST_GUARD)->check()) {
            $guard = GUEST_GUARD;
        } elseif (Auth::guard(ADMIN_GUARD)->check()) {
            $guard = ADMIN_GUARD;
        } else {
            $guard = null;
        }
        $search = $request->input('search');
        $faculty = Faculty::with('faculty_semester')
            ->whereHas('faculty_semester.semester', function (Builder $builder) use ($semester_id) {
                $builder->where("id", $semester_id);
            })
            ->find($faculty_id);
        if (!$faculty) {
            return redirect()->route('student.login');
        }
        $publishing = $this->retrievePublishing($faculty_id, $semester_id, $search, $guard);
        return view('shared.listPublish', [
            'publishes' => $publishing,
            'viewFaculty' => $faculty,
            'semester_id' => $semester_id
        ]);
    }
    private function retrievePublishing($faculty_id, $semester_id, $search, $guard = COORDINATOR_GUARD)
    {
        $faculty = Publish::with('publish_image')
            ->whereHas('article.faculty_semester', function (Builder $builder) use ($faculty_id, $semester_id) {
                $builder->where('faculty_id', $faculty_id)->where('semester_id', $semester_id);
            });
        if ($search) {
            $faculty = $faculty->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%$search%")
                    ->orWhereHas('article.student', function (Builder $builder) use ($search) {
                        $builder->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                    })
                    ->orWhereHas('coordinator', function (Builder $builder) use ($search) {
                        $builder->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                    });
            });
        }
        if ($guard == GUEST_GUARD) {
            $faculty = $faculty->whereHas('article.faculty_semester.faculty.guest',
                function (Builder $builder) use ($guard) {
                    $builder->where('id', Auth::guard($guard)->user()->id);
                });
        }
        return $faculty->orderBy('created_at')->paginate(PER_PAGE);
    }
}
