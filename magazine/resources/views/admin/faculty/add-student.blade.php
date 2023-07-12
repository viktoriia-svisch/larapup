@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty'),route('admin.chooseSemester')) }}
    </div>
@endsection
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
            <hr>
            <h1 class="text-primary">Current Students</h1>
            <p class="text-muted">Display all students within the semester.</p>
            <div class="col-12">
                @if (\Session::has('action_response'))
                    @if (\Session::get('action_response')['status_ok'])
                        <div class="col-md-6 col-12 m-auto">
                            <div class="card bg-success text-white">
                                <div class="card-body" style="padding: 1rem;">
                                    {{\Session::get('action_response')['status_message']}}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6 col-12 m-auto">
                            <div class="card bg-danger text-white">
                                <div class="card-body" style="padding: 1rem;">
                                    {{\Session::get('action_response')['status_message']}}
                                </div>
                            </div>
                        </div>
                    @endif
                    <br>
                @endif
                <br>
            </div>
            @if (count($AvailableStudent) == 0)
            <h2 class="text-center text-muted">No available student</h2>
            @endif
            @foreach($AvailableStudent as $currentstudent)
            @csrf
            <form action="{{route('admin.deleteStudentFaculty', [$currentstudent->id])}}" method="post" >
                {{ csrf_field() }}
                <div class="card mb-2">
                    <div class="card-body row">
                        <div class="col">
                            <div class="col-auto d-flex align-items-center">
                                <h1 class="heading-title">{{$currentstudent->first_name}} {{$currentstudent->last_name}}</h1>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-block m-0 btn-success" id="submit" type="submit">
                                Remove student
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endforeach
                <h1 class="text-primary">Student list</h2>
            @if (count($StudentList) == 0)
                <h2 class="text-center text-muted">No available Student</h2>
            @endif
            @foreach($StudentList as $student)
            @csrf
            <form action="{{route('admin.addStudentFaculty_post', [$FacultySemester->id, $student->id])}}" method="post" >
                {{ csrf_field() }}
                <div class="card mb-2">
                    <div class="card-body row">
                        <div class="col">
                            <div class="col-auto d-flex align-items-center">
                                <h1 class="heading-title">{{$student->first_name}} {{$student->last_name}}</h1>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-block m-0 btn-success" id="submit" type="submit">
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
