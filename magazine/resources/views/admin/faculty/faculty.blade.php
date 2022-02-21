@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
        <div class="col-12 row m-0">
            <div class="col-12">
                <a href="{{route('admin.createFacultySemester')}}" class="btn btn-success btn-block">
                    <i class="fas fa-plus"></i>
                    New Faculty
                </a>
            </div>
        </div>
        <hr>
        @foreach($availableSemester as $semester)
            <h1>{{$semester->name}}</h1>
            <span class="text-muted">Duration: {{$semester->start_date}} - {{$semester->end_date}}</span>
            <br>
            <br>
            @if (count($semester->faculty) == 0)
                <h1 class="m-auto text-muted">No faculty in this semester</h1>
            @else
                @foreach($semester->faculty as $faculty)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h2 class="col-12">{{$faculty->name}}</h2>
                            <p class="font-weight-bold col-12">
                                Deadline:
                                <span class="font-weight-normal">{{$faculty->first_deadline}}</span>
                                -
                                <span class="font-weight-normal">{{$faculty->second_deadline}}</span>
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
            <hr>
            <br>
        @endforeach
    </div>
@endsection
@push("custom-js")
@endpush
