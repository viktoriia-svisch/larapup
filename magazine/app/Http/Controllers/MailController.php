<?php
namespace App\Http\Controllers;
use App\Mail\InformGrading;
use App\Models\Coordinator;
use Illuminate\Support\Facades\Mail;
class MailController extends Controller
{
    public function sendGradingEmail($addressSend,
                                     $coordinator,
                                     $faculty_id,
                                     $semester_id,
                                     $title = 'Faculty submission update')
    {
        Mail::send(new InformGrading($addressSend, $coordinator, $title, $faculty_id, $semester_id));
    }
}
