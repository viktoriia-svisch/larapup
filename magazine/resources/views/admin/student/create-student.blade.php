@extends("layout.Admin.admin-layout")
@section('title', 'Create Student')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.student.create', route('admin.dashboard'), route('admin.student'), route('admin.createStudent')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h2>Create new Student</h2>
        <hr>
        @if (\Session::has('success'))
            @if (\Session::get('success'))
                <div class="col-md-6 col-12 m-auto">
                    <div class="card bg-success text-white">
                        <div class="card-body" style="padding: 1rem;">{{__('message.create_student_success')}}</div>
                    </div>
                </div>
            @else
                <div class="col-md-6 col-12 m-auto">
                    <div class="card bg-danger text-white">
                        <div class="card-body" style="padding: 1rem;">{{__('message.create_student_failed')}}</div>
                    </div>
                </div>
            @endif
            <br>
        @endif
        <form action="{{route('admin.createStudent_post')}}" method="post" class="col-md-6 col-12 m-auto">
            {{csrf_field()}}
            <h3>Login credentials</h3>
            <div class="input-group input-group-alternative mb-3">
                <input type="text" class="form-control form-control-alternative" placeholder="Student login email"
                       aria-label="Email's student" name="email" id="email">
            </div>
            <small class="text-muted mb-4 d-block">
                Student will use this email as their login credential. This email cannot be changed by student nor
                coordinator, only admin is able to update it.
            </small>
            <div class="input-group input-group-alternative mb-3">
                <input type="password" class="form-control form-control-alternative" placeholder="Student password"
                       aria-label="Password's student" name="password" id="password">
            </div>
            <small class="text-muted mb-4 d-block">
                Student password can be updated by student or admin. For admin, there is no requirement for knowing the
                previous password to make update.
            </small>
            <hr>
            <h3>Personal Information</h3>
            <div class="input-group input-group-alternative mb-3">
                <input type="text" class="form-control form-control-alternative" placeholder="Student first name"
                       aria-label="Student's first name" name="first_name" id="first_name">
            </div>
            <div class="input-group input-group-alternative mb-3">
                <input type="text" class="form-control form-control-alternative" placeholder="Student last name"
                       aria-label="Email's student" name="last_name" id="last_name">
            </div>
            <small class="text-muted mb-4 d-block">
                Student is able to update their first name and last name in their profile page after logging in.
            </small>
            <p class="text-muted mt-3 pb-0 mb-1">Gender</p>
            <div class="row m-0 mb-3">
                <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                    <input name="gender" value="{{GENDER['MALE']}}" class="custom-control-input" id="genderMale"
                           type="radio">
                    <label class="custom-control-label" for="genderMale">Male</label>
                </div>
                <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                    <input name="gender" value="{{GENDER['FEMALE']}}" class="custom-control-input" id="genderFemale"
                           checked="" type="radio">
                    <label class="custom-control-label" for="genderFemale">Female</label>
                </div>
            </div>
            <div class="input-group input-group-alternative mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                </div>
                <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth" placeholder="Date of Birth"
                       type="text">
            </div>
            <hr>
            <button class="btn btn-block m-0 btn-success">Create</button>
        </form>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                endDate: "today",
                // endDate: "today",
            });
        })
    </script>
@endpush
