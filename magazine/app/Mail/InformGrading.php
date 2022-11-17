<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class InformGrading extends Mailable
{
    use Queueable, SerializesModels;
    protected $address, $coordinator, $title, $faculty_id, $semester_id;
    public function __construct($address, $coordinator, $title, $faculty_id, $semester_id)
    {
        $this->address = $address;
        $this->coordinator = $coordinator;
        $this->title = $title;
        $this->faculty_id = $faculty_id;
        $this->semester_id = $semester_id;
    }
    public function build()
    {
        return $this->view('Mail.grading_notify')->with([
            'coordinator' => $this->coordinator,
            'semester_id' => $this->semester_id,
            'faculty_id' => $this->faculty_id
        ])->to($this->address);
    }
}
