@extends("layout.Coordinator.coordinator-layout")
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.detail',
         route('coordinator.dashboard'),
         route('coordinator.faculty'),
         route('coordinator.faculty.dashboard',
         [$facultySemester->faculty->id, $facultySemester->id])) }}
    </div>
@endsection
@section("coordinator-content")
    <div class="container">
        <br>
        <div class="text-center col-12 text-muted">
            Faculty
        </div>
        <h1 class="text-center col-12 text-black">{{$facultySemester->faculty->name}}</h1>
        <div class="d-flex justify-content-center align-items-center flex-wrap">
            <a href="{{route('coordinator.faculty.dashboard',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn m-2 text-white @if($site == "dashboard") btn-primary @else bg-gradient-gray @endif">Dashboard</a>
            <a href="{{route('shared.listPublishes',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn m-2 text-white @if($site == "published") btn-primary @else bg-gradient-gray @endif">Publishes</a>
            <a href="{{route('coordinator.faculty.listArticle',[$facultySemester->faculty->id, $facultySemester->semester_id, ])}}"
               class="btn m-2 text-white @if($site == "articles") btn-primary @else bg-gradient-gray @endif">Articles</a>
            <a href="{{route('coordinator.faculty.students',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn m-2 text-white @if($site == "members") btn-primary @else bg-gradient-gray @endif">Members</a>
            <a href="{{route('coordinator.faculty.settings',[$facultySemester->faculty->id, $facultySemester->semester_id])}}"
               class="btn m-2 text-white @if($site == "settings") btn-primary @else bg-gradient-gray @endif">Settings</a>
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
