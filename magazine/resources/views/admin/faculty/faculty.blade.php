@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
        <div class="col-12 row m-0">
            <div class="col-12">
                <a href="{{route('admin.createFacultySemester')}}" class="btn btn-success btn-block">
                    <i class="fas fa-plus"></i>
                    New Faculty
                </a>
            </div>
        </div>
        <hr>
        <form method="get" id="searchBox" action="{{route('admin.faculty')}}" class="col-12 row m-0">
            {{csrf_field()}}
            <div class="form-group col">
                <input type="text" class="form-control form-control-alternative" id="search_faculty_input"
                       name="search_faculty_input"
                       value="@if ($searching) {{$searching}} @endif"
                       placeholder="Type Faculty Name Here">
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
        @if (count($faculties) == 0)
            <h2 class="text-center text-muted">No record found</h2>
        @endif
        @foreach($faculties as $faculty)
            <div class="card mb-2">
                <div class="card-body row">
                    <div class="col">
                        <div class="col-auto d-flex align-items-center">
                            <h1 class="heading-title">{{$faculty->name}}</h1>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <p class="m-0 p-3">
                                Appeared in
                                <span class="text-primary">
                                {{count($faculty->faculty_semester)}}
                                </span>
                                semester(s)
                            </p>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <button class="btn btn-default">
                            Setting
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
        <br>
        <hr>
        <div class="col-12 d-flex justify-content-center">
            {{ $faculties->links() }}
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        function resetSearch() {
            let inputField = $('#search_faculty_input');
            inputField.val('');
            location.href = '{{route('admin.faculty')}}';
        }
    </script>
@endpush
