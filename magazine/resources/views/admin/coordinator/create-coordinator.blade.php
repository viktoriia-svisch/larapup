@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section("coordinator-content")
<div class="container row col-md-12" style="margin-bottom: 10vw">
    <div class="col-sm-5 m-auto">
        <h4 class="title">Create Coordinator</h4>
        <hr>
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
                <input class="form-control" type="text" placeholder="FirstName" name="first_name" id="first_name" required>
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
                <input class="form-control" type="text" placeholder="LastName" name="last_name" id="last_name" required>
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
            <div class="row col-12" style="margin-top: 2vw;">
                <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center" >
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
                <input class="form-control" type="text" placeholder="Email" name="email" id="email" required>
            </div>
            @if($errors->has('password'))
                <p class="col-12 text-danger">
                    {{$errors->first('password')}}
                </p>
            @endif
            <div style="margin-top: 2vw; margin-bottom: 3vw">
                <label style="color: #0b1011">Password</label>
                <input class="form-control" name="password" id="password" value="123456" type="password" placeholder="Password" required>
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
