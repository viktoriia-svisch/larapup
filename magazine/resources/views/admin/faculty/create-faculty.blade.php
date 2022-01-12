@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.create', route('admin.dashboard'), route('admin.faculty'), route('admin.createFacultySemester')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Faculty information</h1>
        <p class="text-muted">New faculty will have information based on the semester (deadlines, applied semester, etc)</p>
        <div class="col-12 row m-0">
            <div class="card col-12 mb-3">
                <div class="card-body">
                    <h2>Semester applied: {{$semester->name}}</h2>
                    <p>Duration: {{$semester->start_date}} - {{$semester->end_date}} </p>
                </div>
            </div>
            <a href="{{route('admin.createFacultySemester')}}" class="btn btn-secondary">
                <i class="fa fa-chevron-circle-left"></i>
                Choose Semester again
            </a>
        </div>
        <hr>
        <form action="{{route('admin.createFaculty_post', [$semester->id])}}" method="post" id="registerFacultyForm" class="col-12 col-md-6 col-sm-8 m-0 m-auto">
            {{csrf_field()}}
            <h2>Faculty information</h2>
            <hr>
            <p class="text-gray">
                Faculty name
            </p>
            <div class="form-group input-group-alternative">
                <input type="text" title="Faculty name" placeholder="Faculty name"
                       class="form-control form-control-alternative" id="name" name="name">
            </div>
            <p class="text-gray">
                First deadline date
            </p>
            <div class="form-group">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input class="form-control datepicker" placeholder="First deadline" type="text" name="first_deadline">
                </div>
            </div>
            <p class="text-gray">
                Second deadline date
            </p>
            <div class="form-group">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input class="form-control datepicker" placeholder="Second deadline" type="text" name="second_deadline">
                </div>
            </div>
            <input type="hidden" id="add_person_option" value="0" name="add_person_option">
            <div class="col-12">
                <button class="btn btn-block col-12 btn-success">
                    Create faculty
                </button>
                <button onclick="secondaryAdding()" class="btn btn-block col-12 btn-default">
                    Create faculty and Add member now
                </button>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'mm/dd/yyyy',
                autoclose:true,
                startDate: "+" + ({{ date_diff(\Carbon\Carbon::now(), date_create($semester->start_date))->format("%a") }} + 2) + "d",
                endDate: "+" + {{ date_diff(\Carbon\Carbon::now(), date_create($semester->end_date))->format("%a") }} + "d",
            });
        });
        function secondaryAdding() {
            $('#add_person_option').val(1);
            $("#registerFacultyForm").submit();
        }
    </script>
@endpush
