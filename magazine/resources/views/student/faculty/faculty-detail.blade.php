@extends("layout.Student.student-layout")
@section('title', 'Faculty Math')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.detail', route('student.dashboard'), route('student.faculty'), $faculty, route('student.faculty.detail', [$faculty->id])) }}
    </div>
@endsection
@section("student-content")
    <div class="container-fluid row">
        <div class="col-sm-12 col-md-8">
            info
        </div>
        <div class="col-sm-12 col-md-4">
            list
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
