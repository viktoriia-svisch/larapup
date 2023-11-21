@extends("layout.Admin.admin-layout")
@section('title', 'Manage Semester')
@push("custom-css")
    <style>
        .border-4 {
            border: 2px solid transparent;
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
            {{ Breadcrumbs::render('dashboard.faculty.create', route('admin.dashboard'), route('admin.faculty'), route('admin.chooseSemester')) }}
    </div>
@endsection
@section('admin-content')
<div class="container">
        <br>
        <h1 class="heading-title">Semester: {{$semester->name}}  </h1>
        <span class="text-gray">
            You can add faculty to this semester in this section and add student to available faculty
        </span>
        <hr>
        <h1 class="text-primary">Current Faculties</h1>
        <p class="text-muted">Display all the faculty within the semester.</p>
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
        @if (count($FacultySemester) == 0)
        <h2 class="text-center text-muted">No available faculty</h2>
        @endif
        @foreach($FacultySemester as $FacuSeme)
            @csrf
            <div class="card mb-2">
                <div class="card-body row">
                    <div class="col">
                        <div class="col-auto d-flex align-items-center">
                            <h1 class="heading-title">{{$FacuSeme->name}}</h1>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <a class="btn btn-block m-0 btn-success" type="button" href="{{route('admin.addStudentFaculty', [$FacuSeme->id])}}">
                            Student list
                        </a>
                        &nbsp;
                        &nbsp;
                        <form action="{{route('admin.deleteSemesterFaculty', [$FacuSeme->id])}}" method="post" >
                            {{ csrf_field() }}
                            <button class="btn btn-block m-0 btn-success" id="submit" type="submit">
                                Remove faculty
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
        <br>
        <h1 class="text-primary">Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
            @if (count($faculty) == 0)
                <h2 class="text-center text-muted">No available faculty</h2>
            @endif
            @foreach($faculty as $Faculty)
            <form action="{{route('admin.addSemesterFaculty', [$semester->id, $Faculty->id])}}" method="post" >
                @csrf
                <div class="card mb-2">
                    <div class="card-body row">
                        <div class="col">
                            <div class="col-auto d-flex align-items-center">
                                <h1 class="heading-title">{{$Faculty->name}}</h1>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-block m-0 btn-success" id="submit" type="submit">
                                Add faculty
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @endforeach
</div>
@endsection
