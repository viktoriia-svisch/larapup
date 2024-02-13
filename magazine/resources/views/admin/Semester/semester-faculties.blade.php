@extends("admin.Semester.semester-detail")
@section('title', $currentSemester->name . ' - Faculties')
@push("custom-css")
@endpush
@section("semester-detail")
    <form method="get" id="searchBox" action="{{route('admin.semesterFaculties', [$currentSemester->id])}}"
          class="col-12 row m-0">
        {{csrf_field()}}
        <div class="form-group col p-0">
            <input type="text" class="form-control form-control-alternative" id="search_faculty_input"
                   name="search"
                   value="@if ($search) {{$search}} @endif"
                   placeholder="Type Faculty Name Here">
        </div>
        <div class="col-auto p-0">
            @if ($search)
                <button type="button" class="btn btn-icon btn-danger" onclick="resetSearch()">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info">
                Search
            </button>
        </div>
    </form>
    <div class="col-12">
        @include('layout.response.errors')
    </div>
    @if (count($faculties) == 0)
        <h2 class="text-center text-muted">No record found</h2>
    @endif
    @foreach($faculties as $faculty)
        <div class="card mb-2">
            <div class="card-body row">
                <div class="col">
                    <div class="col-auto d-flex align-items-center">
                        <h1 class="heading-title">{{$faculty->faculty->name}}</h1>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <p class="m-0 p-3">
                            Student:
                            <span class="text-primary">
                                {{count($faculty->faculty_semester_student)}}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                </div>
            </div>
        </div>
    @endforeach
    <hr>
    <div class="col-12 d-flex justify-content-center">
        {{ $faculties->links() }}
    </div>
@endsection
@push("custom-js")
@endpush
