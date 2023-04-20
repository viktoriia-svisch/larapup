@extends("layout.Admin.admin-layout")
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
        {{ Breadcrumbs::render('dashboard.semester', route('admin.dashboard'), route('admin.semester')) }}
    </div>
@endsection
@section('admin-content')
    <div class="container">
        <div class="col-12 row m-0">
            <div class="col-12 col-sm-6 mt-4">
                <a href="{{route('admin.createSemester')}}" class="btn btn-success btn-block">
                    <i class="fas fa-plus"></i>
                    New Semester
                </a>
            </div>
            <div class="col-12 col-sm-6 mt-4">
                <a href="#" class="btn btn-default btn-block">
                    <i class="fas fa-cog"></i>
                    Setup Semester
                </a>
            </div>
        </div>
        <hr> 
        <form method="get" action="{{route('admin.semester')}}" class="col-12 row m-0">
            {{csrf_field()}}
            <div class="form-group col">
                <input type="text" class="form-control form-control-alternative" id="search_semester_input"
                       name="search_semester_input"
                       value="@if ($searching) {{$searching}} @endif"
                       placeholder="Type Semester Name or Year Here">
            </div>
            <div class="col-auto p-0">
                @if ($searching)
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
        <br>
        <div class="col-12">
            <h1>Active Semester</h1>
            @if ($activeSemester)
                <div class="card mb-4 border-4 border-success">
                    <div class="card-body row m-0">
                        <div class="col row p-0 m-0">
                            <div class="col row d-flex align-items-center">
                                <h2 class="col-12 heading-title">{{$activeSemester->name}}</h2>
                                <p class="col-12 m-0">
                                    {{$activeSemester->description}}
                                </p>
                            </div>
                            <div class="col-auto row m-0 d-flex align-items-center">
                                <div class="col-12 col-md-5 d-flex align-items-center">
                                    <h3 class="mb-0">
                                        Start: {{\App\Helpers\DateTimeHelper::formatDate($activeSemester->start_date)}}</h3>
                                </div>
                                <div class="col-12 col-md-5 d-flex align-items-center">
                                    <h3 class="mb-0">
                                        End: {{\App\Helpers\DateTimeHelper::formatDate($activeSemester->end_date)}}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-icon btn-default" type="button">
                                <i class="fas fa-cog top-0"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <h2 class="text-muted">No active semester currently</h2>
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
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-icon btn-default" type="button">
                                <i class="fas fa-cog top-0"></i>
                            </button>
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
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-icon btn-default" type="button">
                                <i class="fas fa-cog top-0"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
            @if (count($pastSemester) == 0)
                <h2 class="text-muted m-auto">No record found</h2>
            @endif
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        function resetSearch() {
            let inputField = $('#search_semester_input');
            inputField.val('');
            location.href = '{{route('admin.semester')}}';
        }
    </script>
@endpush
