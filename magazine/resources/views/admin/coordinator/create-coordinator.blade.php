@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section("coordinator-content")
<div class="container row col-md-12" style="margin-bottom: 10vw">
    <div class="col-sm-1">
    </div>
    <div class="col-sm-5">
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
            <div class="row col-xl-12" style="margin-top: 2vw; margin-right: -1vw">
                <h6 class="col-xl-12" style="color: #0b1011; margin-bottom: 2vw;">Gender</h6>
                <input class="col-xl-1" type="radio" name="gender" value="{{GENDER['MALE']}}" id="genderMale" required>
                <p class="col-xl-2" style="margin-top: -0.30vw">Male</p>
                <input class="col-xl-1" type="radio" name="gender" value="{{GENDER['FEMALE']}}" id="genderFemale"required>
                <p class="col" style="margin-top: -0.30vw">Female</p>
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
                <input class="form-control" name="password" id="password" type="text" placeholder="Password" required>
            </div>
            <hr>
            <button class="btn btn-danger col-sm-12">Create</button>
        </form>
    </div>
    <div class="col-sm-2">
    </div>
    <div class="col-sm-4">
        <h4 style="text-align: center">List Coordinator</h4>
        <input style="margin-top: 1vw" class="form-control" type="text" placeholder="search">
        <hr>
        <div class="row col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
            <label class="col-xl-7">Mrs Dương</label>
            <button class=" btn-primary col-xl-5">Edit Student</button>
        </div>
        <div class="row col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
            <label class="col-xl-7">Mr Tùng</label>
            <button class=" btn-primary col-xl-5">Edit Student</button>
        </div>
        <div class="row col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
            <label class="col-xl-7">Mr Bình</label>
            <button class=" btn-primary col-xl-5">Edit Student</button>
        </div>
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
