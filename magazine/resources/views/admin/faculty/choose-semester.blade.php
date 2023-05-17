@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.create', route('admin.dashboard'), route('admin.faculty'), route('admin.chooseSemester')) }}
</div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Choosing semester</h1>
        <p class="text-muted">After choosing semester, you will then input information of new faculty</p>
        <hr>
        @foreach($futureSemester as $semester)
            <a class="card mb-3">
                <div class="card-body">
                    <h2 class="col-12">{{$semester->name}}</h2>
                    <p class="font-weight-bold">
                        Duration:
                        <span class="font-weight-normal">{{$semester->start_date}}</span>
                        <span class="font-weight-normal">{{$semester->end_date}}</span>
                    </p>
                </div>
                <a class="btn btn-block m-0 btn-success" type="button" href="{{route('admin.chooseSemesterFaculty', [$semester->id])}}">
                        Select Semester
                </a>
            </a>
        @endforeach
@endsection
@push("custom-js")
@endpush
