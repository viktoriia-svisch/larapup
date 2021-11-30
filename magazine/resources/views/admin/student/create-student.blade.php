@extends("layout.Admin.admin-layout")
@section('title', 'Create Student')
@push("custom-css")
    <style>
        .addons-alternative {
            background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABHklEQVQ4EaVTO26DQBD1ohQWaS2lg9JybZ+AK7hNwx2oIoVf4UPQ0Lj1FdKktevIpel8AKNUkDcWMxpgSaIEaTVv3sx7uztiTdu2s/98DywOw3Dued4Who/M2aIx5lZV1aEsy0+qiwHELyi+Ytl0PQ69SxAxkWIA4RMRTdNsKE59juMcuZd6xIAFeZ6fGCdJ8kY4y7KAuTRNGd7jyEBXsdOPE3a0QGPsniOnnYMO67LgSQN9T41F2QGrQRRFCwyzoIF2qyBuKKbcOgPXdVeY9rMWgNsjf9ccYesJhk3f5dYT1HX9gR0LLQR30TnjkUEcx2uIuS4RnI+aj6sJR0AM8AaumPaM/rRehyWhXqbFAA9kh3/8/NvHxAYGAsZ/il8IalkCLBfNVAAAAABJRU5ErkJggg==");
            background-repeat: no-repeat;
            background-attachment: scroll;
            background-size: 16px 18px;
            background-position: 98% 50%;
            cursor: auto;
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('student_create', route('admin.dashboard'), route('admin.student'), route('admin.createStudent')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h2>Create new Student</h2>
        <hr>
        <form method="post" class="col-md-6 col-12 m-auto">
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
                    <input name="custom-radio-2" class="custom-control-input" id="genderMale" type="radio">
                    <label class="custom-control-label" for="genderMale">Male</label>
                </div>
                <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                    <input name="custom-radio-2" class="custom-control-input" id="genderFemale" checked="" type="radio">
                    <label class="custom-control-label" for="genderFemale">Female</label>
                </div>
            </div>
            <div class="input-group input-group-alternative mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                </div>
                <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth" placeholder="Date of Birth" type="text">
            </div>
            <hr>
            <button class="btn btn-block m-0 btn-success">Create</button>
        </form>
    </div>
@endsection
@push("custom-js")
@endpush
