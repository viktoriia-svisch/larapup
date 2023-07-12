@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.create', route('admin.dashboard'), route('admin.faculty'), route('admin.chooseSemester')) }}
</div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Please choose the semester first</h1>
        <p class="text-muted">After choosing semester, you will then input information of new faculty</p>
        <hr>
        <form method="get" action="{{route('admin.semesterSearch')}}" class="col-12 row m-0">
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
        @if (count($futureSemester) == 0)
        <h2 class="text-center text-muted">No record found</h2>
        @endif
        @foreach($futureSemester as $semester)
            <a class="card mb-3">
                <div class="card-body">
                    <h2 class="col-12">{{$semester->name}}</h2>
                    <p class="font-weight-bold">
                        Duration:
                        <span class="font-weight-normal">{{$semester->start_date}}</span>
                        <span class="font-weight-normal">{{$semester->end_date}}</span>
                    </p>
                </div>
                <a class="btn btn-block m-0 btn-success" type="button" href="{{route('admin.chooseSemesterFaculty', [$semester->id])}}">
                        Select Semester
                </a>
            </a>
        @endforeach
@endsection
@push("custom-js")
    <script>
        function resetSearch() {
            let inputField = $('#search_semester_input');
            inputField.val('');
            location.href = '{{route('admin.chooseSemester')}}';
        }
    </script>
@endpush
