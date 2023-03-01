<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class GeneralController extends FacultySemesterBaseController
{
    public function published(Request $request, $id_publish){
        return view('shared.publish');
    }
    public function listPublished(Request $request){
        return view('shared.listPublish');
    }
}
