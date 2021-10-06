<?php

namespace App\Http\Controllers\Coordinator;


use App\Http\Controllers\Controller;
use App\Http\Resources\Coordinator as CoordinatorResource;
use App\Http\Resources\Faculty as FacultyResource;
use App\Models\Coordinator;
use App\Models\Faculty;
use App\Models\Semester;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CoordinatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $coordinators = Coordinator::paginate(PER_PAGE);
        return CoordinatorResource::collection($coordinators);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    public function storeFaculty(CreateFaculty $request)
    {
        $coor = new Faculty();

        $coor->semester_id= $request->get('semester_id');
        $coor->name = $request->get('name');
        $coor->first_deadline = $request->get('first_deadline');
        $coor->second_deadline = $request->get('second_deadline');

        $validated = $request->validated();

        $checksemesterId = \DB::table('semesters') //retrieve semester from semester_id from faculty table
        ->select('semesters.id')
        ->where('semesters.id',$coor->semester_id)
        ->first();

        $getSemesterDate = \DB::table('semesters') //retrieve semester from semester_id from faculty table
        ->where('id','=',$coor->semester_id)
        ->first();
        
        if(!$checksemesterId) //check if the faculty is created from exist semester or not
        {
            return $this->responseMessage('There is no semester with id '.$coor->semester_id. " .Please create one first", true);
        }

        if($coor->first_deadline < $getSemesterDate->start_date || $coor->first_deadline > $getSemesterDate->end_date) //check faculty date within semester date
         {
              return $this->responseMessage('Please enter the date between semester start and end date',true);
         }

        if($coor->second_deadline < $getSemesterDate->start_date || $coor->second_deadline > $getSemesterDate->end_date)//check faculty date within semester date
         {
              return $this->responseMessage('Please enter the date between semester start and end date',true);
         }

        else if ($coor->save())
            {
                return $this->responseMessage(
                    'New faculty created successfully',
                    false,
                    'success',
                    $coor,
                );
            }
        


    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return CoordinatorResource
     */
    public function show($id)
    {
        $coordinator = Coordinator::find($id);
        return new CoordinatorResource($coordinator);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
