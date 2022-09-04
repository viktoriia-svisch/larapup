@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty'), route('admin.chooseSemester')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Choosing semester</h1>
        <p class="text-muted">After choosing semester, you will then input information of new faculty</p>
        <hr>
        <div class="col-sm-6">
            <a class="btn btn-block m-0 btn-success" href="{{route('admin.addStudentFaculty')}}">Test</a>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
