@extends("layout.Admin.admin-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.coordinator.create', route('admin.dashboard'), route('admin.coordinator'), route('admin.createCoordinator')) }}
    </div>
@endsection
@section("admin-content")
<div class="container row col-md-12" style="margin-bottom: 10vw">
    <div class="col-sm-5 m-auto">
        <h1 class="title">Create Coordinator</h1>
        <hr>
        @include("layout.response.errors")
        <form action="{{route('admin.createCoordinator_post')}}" method="post">
            {{csrf_field()}}
            <label style="color: #0b1011">First Name</label>
            @if($errors->has('first_name'))
                <div class="card bg-danger text-white rounded-0">
                    <div class="card-body p-1 rounded-0">
                        {{$errors->first('first_name')}}
                    </div>
                </div>
            @endif
            <div  >
                <input class="form-control" type="text" placeholder="FirstName" name="first_name" id="first_name">
            </div>
            @if($errors->has('last_name'))
                <div class="card bg-danger text-white rounded-0">
                    <div class="card-body p-1 rounded-0">
                        {{$errors->first('last_name')}}
                    </div>
                </div>
            @endif
            <div style="margin-top: 2vw">
                <label style="color: #0b1011">Last Name</label>
                <input class="form-control" type="text" placeholder="LastName" name="last_name" id="last_name">
            </div>
            @if($errors->has('dateOfBirth'))
                <div class="card bg-danger text-white rounded-0">
                    <div class="card-body p-1 rounded-0">
                        {{$errors->first('dateOfBirth')}}
                    </div>
                </div>
            @endif
            <div style="margin-top: 2vw">
                <label style="color: #0b1011">Date of Birth</label>
                <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth" placeholder="Date of Birth"
                       type="text">
            </div>
            @if($errors->has('gender'))
                <p class="col-12 text-danger">
                    {{$errors->first('gender')}}
                </p>
            @endif
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
            @if($errors->has('email'))
                <p class="col-12 text-danger">
                    {{$errors->first('email')}}
                </p>
            @endif
            <div style="margin-top: 2vw">
                <label style="color: #0b1011">Email</label>
                <input class="form-control" type="text" placeholder="Email" name="email" id="email">
            </div>
            @if($errors->has('password'))
                <p class="col-12 text-danger">
                    {{$errors->first('password')}}
                </p>
            @endif
            <div style="margin-top: 2vw; margin-bottom: 3vw">
                <label style="color: #0b1011">Password</label>
                <input class="form-control" name="password" id="password" value="123456" type="password" placeholder="Password">
            </div>
            <hr>
            <button class="btn btn-danger col-sm-12">Create</button>
        </form>
    </div>
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
