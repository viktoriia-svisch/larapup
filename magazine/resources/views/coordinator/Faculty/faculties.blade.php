@extends("layout.Coordinator.coordinator-layout")
@section('title', 'All Faculties')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('coordinator.coordinator'), route('coordinator.faculty')) }}
    </div>
@endsection
@section("coordinator-content")
    <div class="container">
        <h1>Current Active faculty</h1>
        <br>
        @if ($currentFaculty !== null)
            <div class="card">
                <div class="card-body row">
                    <div class="col-12 col-sm-6 col-md-5 d-flex align-items-center">
                        <h2>{{$currentFaculty->faculty->name}}</h2>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <h3 class="text-muted">Deadline</h3>
                        <p>First Deadline:
                            <span class="font-weight-bold">
                            {{\App\Helpers\DateTimeHelper::formatDateTime($currentFaculty->first_deadline)}}
                        </span>
                        </p>
                        <p>Second Deadline:
                            <span class="font-weight-bold">
                            {{\App\Helpers\DateTimeHelper::formatDateTime($currentFaculty->second_deadline)}}
                        </span>
                        </p>
                    </div>
                    <div class="col-12 col-sm-12 col-md-auto d-flex align-items-center">
                        <a href="{{route('coordinator.faculty.dashboard', [$currentFaculty->faculty->id, $currentFaculty->semester->id])}}"
                           class="btn btn-primary">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        @else
            <h2 class="text-muted m-auto">No activity yet</h2>
            <br>
        @endif
        <hr>
        <form method="get" id="searchBox" action="{{route('coordinator.faculty')}}" class="col-12 row m-0">
            {{csrf_field()}}
            <div class="form-group col">
                <input type="text" class="form-control form-control-alternative" id="search_faculty_input"
                       name="search_faculty_input"
                       value="@if ($searchTerms) {{$searchTerms}} @endif"
                       placeholder="Type Faculty Name Here">
                <input type="hidden" name="viewMode" id="viewMode" value="{{$viewMode}}">
            </div>
            <div class="col-auto p-0">
                @if ($searchTerms)
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
        <div class="col-12 row">
            <h2 class="col-auto mb-0 d-flex align-items-center">Filter Faculty:</h2>
            <div class="col">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="viewModeButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @if ($viewMode == 0)
                            All
                        @elseif ($viewMode == 1)
                            Incoming
                        @else
                            Ended
                        @endif
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item cursor @if($viewMode == 0) selected bg-primary text-white @endif"
                           onclick="selectMode(0)">
                            All
                        </a>
                        <a class="dropdown-item cursor @if($viewMode == 1) selected bg-primary text-white @endif"
                           onclick="selectMode(1)">
                            Incoming
                        </a>
                        <a class="dropdown-item cursor @if($viewMode == 2) selected bg-primary text-white @endif"
                           onclick="selectMode(2)">
                            Ended
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="col-12">
            @if (count($faculties) > 0)
                @foreach($faculties as $facultySemester)
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="col">
                                <h2>{{$facultySemester->faculty->name}}</h2>
                            </div>
                            <div class="col-auto">
                            </div>
                            <div class="col d-flex justify-content-center align-items-center">
                                <a href="{{route("coordinator.faculty.dashboard", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
                                   class="btn btn-secondary"></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <h2 class="text-muted m-auto">No record found</h2>
            @endif
            <hr>
            <div class="col-12 d-flex justify-content-center">
                {{$faculties->appends(['viewMode' => $viewMode, 'search_faculty_input'=>$searchTerms])->links()}}
            </div>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        let inputField = $('#search_faculty_input');
        let facultyMode = $('#viewMode');
        let formSearch = $("#searchBox");
        function resetSearch() {
            inputField.val('');
            facultyMode.val(0);
            location.href = '{{route('coordinator.faculty')}}';
        }
        function selectMode(mode) {
            if (mode === 1 || mode === 2 || mode === 0) {
                facultyMode.val(mode);
                formSearch.submit();
            }
        }
    </script>
@endpush
