@extends("layout.Student.student-layout")
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.detail',
         route('student.dashboard'),
         route('student.faculty'),
         route('student.faculty.dashboard',
         [$facultySemester->faculty->id, $facultySemester->id])) }}
    </div>
@endsection
@section("student-content")
    <div class="container">
        <br>
        <div class="text-center col-12 text-muted">
            Faculty
        </div>
        <h1 class="text-center col-12 text-black">{{$facultySemester->faculty->name}}</h1>
        <div class="d-flex justify-content-center align-items-center">
            <a href="{{route('student.faculty.dashboard',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn text-white @if($site == "dashboard") btn-primary @else bg-gradient-gray @endif">Dashboard</a>
            <a href="{{route('student.faculty.article',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn text-white @if($site == "articles") btn-primary @else bg-gradient-gray @endif">Article</a>
            <a href="{{route('student.faculty.members',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn text-white @if($site == "members") btn-primary @else bg-gradient-gray @endif">Members</a>
        </div>
        <hr>
        <div class="col-12">
            <span class="text-muted font-weight-bold">
                Topic:
            </span>
            <span>
                {{nl2br($facultySemester->description)}}
            </span>
        </div>
        <hr>
        @yield('faculty-detail')
    </div>
@endsection
@push("custom-js")
@endpush
