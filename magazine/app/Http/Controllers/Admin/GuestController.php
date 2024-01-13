<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGuest;
use App\Http\Requests\UpdateGuestByAdmin;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\FacultySemesterCoordinator;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class GuestController extends Controller
{
    public function guest(Request $request)
    {
        $searchTerms = $request->get('search_guest_input');
        $searchType = $request->get('type');
        $guestList = Guest::with('faculty');
        if ($searchType != -1 && $searchType != null) {
            $guestList->where('status', $request->get('type'));
        }
        if ($searchTerms != null) {
            $guestList->whereHas('faculty', function (Builder $builder) use ($searchTerms) {
                $builder->where('name', 'like', '%' . $searchTerms . '%');
            })->get();
        }
        return view('admin.guest.guest', ['guests' => $guestList->paginate(PER_PAGE)]);
    }
    public function createGuest_post(CreateGuest $request)
    {
        $guest = new Guest($request->all([
            'email',
            'password',
            'faculty_id'
        ]));
        if ($guest->save())
            return back()->with($this->responseBladeMessage(__('Create guest account success!')));
        return back()->with($this->responseBladeMessage(__('Create guest account failed!'), false));
    }
    public function create()
    {
        $faculties = Faculty::get();
        return view('admin.guest.create-guest', ['faculties' => $faculties]);
    }
    public function updateGuest($id)
    {
        $guest = Guest::with("faculty")->find($id);
        if ($guest)
            return view('admin.guest.active-guest', [
                'guest' => $guest
            ]);
        return redirect()->back()->with($this->responseBladeMessage("Unable to find the Guest", false));
    }
    public function updateGuestPost(UpdateGuestByAdmin $request, $id)
    {
        $guest= Guest::with("faculty")->find($id);
        if (!$guest)
            return redirect()->back()->with($this->responseBladeMessage("Unable to find the guest", false));
        $guest->status = $request->get('status') ?? $guest->status;
        $guest->email = $request->get('email') ?? $guest->email;
        if ($request->get('old_password')) {
            if (Hash::check($request->get('old_password'), $guest->password)) {
                $guest->password = $request->get('new_password');
            } else {
                return redirect()->back()->with($this->responseBladeMessage("Update Guest failed", false));;
            }
        }
        if ($guest->save()) {
            return redirect()->back()->with($this->responseBladeMessage("Update Guest success"));;
        }
        return redirect()->back()->with($this->responseBladeMessage("Update Guest failed", false));;
    }
}
