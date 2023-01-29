@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section("admin-content")
<div class="container">
        <br>
        @foreach($semester as $sem)
        @foreach($faculty as $fac)
        <h1 class="heading-title">Faculty {{$fac->name}} of semester {{$sem->name}} </h1>
        @endforeach
        @endforeach
            <span class="text-gray">
                You can add student to faculty of specific semester
            </span>
            <h1 class="text-primary">Current Students</h1>
            <p class="text-muted">Display all the faculty within the semester.</p>
            @if (count($AvailableStudent) == 0)
            <h2 class="text-center text-muted">No record found</h2>
            @endif
            @foreach($AvailableStudent as $currentstudent)
            @csrf
            <div class="card mb-2">
                <div class="card-body row">
                    <div class="col">
                        <div class="col-auto d-flex align-items-center">
                            <h1 class="heading-title">{{$currentstudent->first_name}} {{$currentstudent->last_name}}</h1>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <a class="btn btn-block m-0 btn-success" type="button">
                            Remove student
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
                <h1 class="text-primary">Student list</h1>
            @if (count($StudentList) == 0)
                <h2 class="text-center text-muted">No record found</h2>
            @endif
            @foreach($StudentList as $student)
            <form action="{{route('coordinator.addStudentFaculty_post', [$FacultySemester->id, $student->id])}}" method="post" >
                @csrf
                <div class="card mb-2">
                    <div class="card-body row">
                        <div class="col">
                            <div class="col-auto d-flex align-items-center">
                                <h1 class="heading-title">{{$student->first_name}} {{$student->last_name}}</h1>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-block m-0 btn-success" id="submit" type="submit" >
                                Add student
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @endforeach
</div>
@endsection
@push("custom-js")
@endpush
