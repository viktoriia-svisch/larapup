<?php
namespace Illuminate\Broadcasting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Broadcast;
class BroadcastController extends Controller
{
    public function authenticate(Request $request)
    {
        if ($request->hasSession()) {
            $request->session()->reflash();
        }
        return Broadcast::auth($request);
    }
}
