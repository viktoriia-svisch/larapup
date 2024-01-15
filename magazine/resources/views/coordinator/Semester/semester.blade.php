@extends("layout.Coordinator.coordinator-layout")
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
        {{ Breadcrumbs::render('dashboard.semester', route('coordinator.dashboard'), route('coordinator.manageSemester')) }}
    </div>
@endsection
@section('coordinator-content')
    <div class="container">
        <hr>
        <h1>
            In-charge semester of
            {{\Illuminate\Support\Facades\Auth::user()->first_name . ' '.\Illuminate\Support\Facades\Auth::user()->last_name}}
        </h1>
        <form method="get" action="{{route('coordinator.manageSemester')}}" class="col-12 row m-0">
            {{csrf_field()}}
            <div class="form-group col">
                <input type="text" class="form-control form-control-alternative" id="search_semester_input"
                       name="search"
                       value="@if ($search) {{$search}} @endif"
                       placeholder="Type Semester Name or Year Here">
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
        <hr>
        <div class="col-12">
            @include('layout.response.errors')
        </div>
        <h1>Current Semester</h1>
        @if($activeSemester)
            <div class="card mb-4 border-4 border-secondary">
                <div class="card-body row m-0">
                    <div class="col row p-0 m-3">
                        <div class="col row d-flex align-items-center">
                            <h2 class="col-12 heading-title">{{$activeSemester->name}}</h2>
                            <p class="col-12 m-0">
                            </p>
                        </div>
                        <div class="col-auto row m-0 d-flex align-items-center">
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0 m-3">
                                    Start:{{\App\Helpers\DateTimeHelper::formatDate($activeSemester->start_date)}}</h3>
                            </div>
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0 m-3">
                                    End:{{\App\Helpers\DateTimeHelper::formatDate($activeSemester->end_date)}} </h3>
                            </div>
                        </div>
                        @if (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER'])
                            <div class="col-12">
                                <p class="text-muted p-0">
                                    Faculties:
                                    @if (sizeof($activeSemester->faculty_semester) == 0)
                                        <span class="text-danger">
                                            None
                                        </span>
                                    @endif
                                    @foreach($activeSemester->faculty_semester as $faculty)
                                        <span class="text-info mr-4">
                                            {{$faculty->faculty->name}}
                                        </span>
                                    @endforeach
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <a class="btn btn-icon-only btn-info"
                           href="{{route('coordinator.semester.detail', [$activeSemester->id])}}">
                            <i class="fas fa-info top-0"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <hr>
        <h1>Future Semester</h1>
        @foreach($futureSemester as $semester)
            <div class="card mb-4 border-4 border-warning">
                <div class="card-body row m-0">
                    <div class="col row p-0 m-0">
                        <div class="col row d-flex align-items-center">
                            <h2 class="col-12 heading-title">{{$semester->name}}</h2>
                            <p class="col-12 m-0">
                                {{$semester->description}}
                            </p>
                        </div>
                        <div class="col-auto row m-0 d-flex align-items-center">
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0">
                                    Start: {{\App\Helpers\DateTimeHelper::formatDate($semester->start_date)}}</h3>
                            </div>
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0">
                                    End: {{\App\Helpers\DateTimeHelper::formatDate($semester->end_date)}}</h3>
                            </div>
                        </div>
                        @if (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER'])
                            <div class="col-12">
                                <p class="text-muted p-0">
                                    Faculties:
                                    @if (sizeof($semester->faculty_semester) == 0)
                                        <span class="text-danger">
                                            None
                                        </span>
                                    @endif
                                    @foreach($semester->faculty_semester as $faculty)
                                        <span class="text-info mr-4">
                                            {{$faculty->faculty->name}}
                                        </span>
                                    @endforeach
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <a class="btn btn-icon-only btn-info"
                           href="{{route('coordinator.semester.detail', [$semester->id])}}">
                            <i class="fas fa-info top-0"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
        @if (count($futureSemester) == 0)
            <h2 class="text-muted m-auto">No record found</h2>
        @endif
        <hr>
        <h1>Past Semester</h1>
        @foreach($pastSemester as $semester)
            <div class="card mb-4 border-4 border-secondary">
                <div class="card-body row m-0">
                    <div class="col row p-0 m-0">
                        <div class="col row d-flex align-items-center">
                            <h2 class="col-12 heading-title">{{$semester->name}}</h2>
                            <p class="col-12 m-0">
                                {{$semester->description}}
                            </p>
                        </div>
                        <div class="col-auto row m-0 d-flex align-items-center">
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0">
                                    Start: {{\App\Helpers\DateTimeHelper::formatDate($semester->start_date)}}</h3>
                            </div>
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0">
                                    End: {{\App\Helpers\DateTimeHelper::formatDate($semester->end_date)}}</h3>
                            </div>
                        </div>
                        @if (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER'])
                            <div class="col-12">
                                <p class="text-muted p-0">
                                    Faculties:
                                    @if (sizeof($semester->faculty_semester) == 0)
                                        <span class="text-danger">
                                            None
                                        </span>
                                    @endif
                                    @foreach($semester->faculty_semester as $faculty)
                                        <span class="text-info mr-4">
                                            {{$faculty->faculty->name}}
                                        </span>
                                    @endforeach
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <a class="btn btn-icon-only btn-info"
                           href="{{route('coordinator.semester.detail', [$semester->id])}}">
                            <i class="fas fa-info top-0"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
        @if (count($pastSemester) == 0)
            <h2 class="text-muted m-auto">No record found</h2>
        @endif
    </div>
@endsection
@push("custom-js")
    <script>
        function resetSearch() {
            let inputField = $('#search_semester_input');
            inputField.val('');
            location.href = '{{route('coordinator.manageSemester')}}';
        }
    </script>
@endpush
