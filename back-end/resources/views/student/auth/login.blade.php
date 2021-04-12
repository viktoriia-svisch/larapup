@extends("layout.master")
@section('title', 'Login')
@push("custom-css")
@endpush
@section("content")
    <div class="height-fluid d-flex justify-content-center align-items-center">
        <form class="col-md-3 col-xs-12" method="post" action="">
            {{ csrf_field() }}
            <div class="col-12">
                <h1>Student portal</h1>
                <div class="form-group">
                    <div class="input-group input-group-alternative mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        </div>
                        <input class="form-control form-control-alternative" placeholder="Enter your email here" name="email"
                               id="school_id" type="email">
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
                    @if($errors->has('code'))
                        <p class="col-12 text-danger">
                            {{$errors->first('code')}}
                        </p>
                    @elseif($errors->has('password'))
                        <p class="col-12 text-danger">
                            {{$errors->first('password')}}
                        </p>
                    @endif
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    <div class="col-sm">
                        <a href="" class="float-right text-underline">Forgot your password?</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
@endpush
