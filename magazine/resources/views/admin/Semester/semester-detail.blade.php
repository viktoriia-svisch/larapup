@extends("layout.Admin.admin-layout")
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container">
        <h1 class="text-center">
            Semester {{$currentSemester->name}}
        </h1>
        <div class="col-12 mb-4">
            <small class="text-muted">
                Semester's Note: {{$currentSemester->description}}
            </small>
        </div>
        <div class="d-flex flex-wrap justify-content-start align-items-center">
            <a href="{{route("admin.semesterDetail", [$currentSemester->id])}}" class="btn btn-secondary m-1">
                Statistic
            </a>
            <a href="{{route("admin.semesterFaculties", [$currentSemester->id])}}" class="btn btn-secondary m-1">
                Faculties
            </a>
            <a href="{{route("admin.updateSemester", [$currentSemester->id])}}" class="btn btn-secondary m-1">
                About
            </a>
        </div>
        <hr class="mt-1">
        @include('layout.response.errors')
        @yield('semester-detail')
    </div>
@endsection
@push("custom-js")
@endpush
