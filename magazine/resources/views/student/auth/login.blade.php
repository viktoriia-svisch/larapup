@extends("layout.master")
@section('title', 'Login')
@push("custom-css")
@endpush
@section("content")
    <div class="height-fluid d-flex justify-content-center align-items-center bg-primary">
        <form class="col-12 col-md-10" style="max-width: 450px" method="post" action="{{route('student.loginPost')}}">
            {{ csrf_field() }}
            <div class="col-12">
                <h1 class="text-white">Student portal</h1>
                <div class="form-group">
                    <div class="input-group input-group-alternative mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        </div>
                        <input class="form-control form-control-alternative" placeholder="Enter your email here" name="email"
                               id="studentEmail" type="email">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group input-group-alternative mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        </div>
                        <input class="form-control form-control-alternative" id="password" name="password"
                               placeholder="Enter your password here" type="password">
                    </div>
                </div>
                <div class="form-group row">
                    @if($errors->has('email'))
                        <p class="col-12 text-red">
                            {{$errors->first('email')}}
                        </p>
                    @elseif($errors->has('password'))
                        <p class="col-12 text-red">
                            {{$errors->first('password')}}
                        </p>
                    @endif
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-block btn-default">Login</button>
                    </div>
                    <div class="col-sm">
                        <a href="" class="float-right text-white">Forgot your password?</a>
                    </div>
                </div>
                <hr>
                <div class="col-12 row p-0 m-0">
                    <a href="{{route('guest.login')}}" class="btn btn-block btn-neutral mr-0">Login as guest</a>
                    <a href="{{route('coordinator.login')}}" class="btn btn-block btn-neutral">Login as coordinator</a>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
@endpush
