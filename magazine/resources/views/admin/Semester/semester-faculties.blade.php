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
                <a class="btn btn-icon btn-danger" href="{{route("admin.semesterFaculties", [$currentSemester->id])}}">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info">
                Search
            </button>
        </div>
    </form>
    <h1 class="text-primary">Current Faculties</h1>
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
    @if(!\App\Helpers\DateTimeHelper::time1BeforeTime2($currentSemester->start_date,Carbon::now()))
        <h1 class="text-primary">Available Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
        @if (count($facultyList) == 0)
            <h2 class="text-center text-muted">No available faculty</h2>
        @endif
        @foreach($facultyList as $Faculty)
            <form action="{{route('admin.addSemesterFaculty', [$currentSemester->id, $Faculty->id])}}" method="post">
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
    @endif
    <hr>
    <div class="col-12 d-flex justify-content-center">
        {{ $facultyList->links() }}
    </div>
@endsection
@push("custom-js")
@endpush
