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
    <div class="row col-12">
        <div class="col-sm-6">
                <h1 class="text-primary">Add new faculty</h1>
                        <div class="col-12 m-0 p-0">
                                <button class="btn btn-block m-0 btn-success" data-toggle="modal" data-target="#modal-form">Create</button>
                        </div>
         </div>
            <div class="col-sm-6">
                <h1 class="text-primary">Add student into faculty</h1>
                <div class="col-12 m-0 p-0">
                    <a class="btn btn-block m-0 btn-success" href="{{route('admin.chooseSemester')}}">Add student</a>
                </div>
            </div>
    </div>
        <br>
        <hr>
        <h1 class="text-primary">Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
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
@section('modal')
    <div class="col-md-4" id="modal">
        <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
            <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="card bg-secondary shadow border-0">
                            <div class="card-body px-lg-5 py-lg-5">
                                <form action="{{route('admin.createFaculty_post')}}" method="post">
                                {{csrf_field()}}
                                    <h2 class="text-primary">Faculty name</h2>
                                    <div class="form-group input-group-alternative">
                                    <input id="name" type="text" title="Faculty name" placeholder="Faculty name"
                                    class="form-control form-control-alternative" id="name" name="name">
                                    </div>
                                <div class="col-12 m-0 p-0">
                                    <button class="btn btn-block m-0 btn-success" id="submit" type="submit"
                                            data-toggle="modal" data-target="#modal-form">Submit</button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
