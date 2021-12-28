@extends("layout.Admin.admin-layout")
@section('title', 'Manage Student')
@push("custom-css")
    <style>
        .row.heading h2 {
            color: #fff;
            font-size: 52.52px;
            line-height: 95px;
            font-weight: 400;
            text-align: center;
            margin: 0 0 40px;
            padding-bottom: 20px;
            text-transform: uppercase;
        }
        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .heading.heading-icon {
            display: block;
        }
        .padding-lg {
            display: block;
            padding-top: 60px;
            padding-bottom: 60px;
        }
        .practice-area.padding-lg {
            padding-bottom: 55px;
            padding-top: 55px;
        }
        .practice-area .inner {
            border: 1px solid #999999;
            text-align: center;
            margin-bottom: 28px;
            padding: 40px 25px;
        }
        .cnt-block:hover {
            border-color: rgba(0, 0, 0, 0.3);
        }
        .practice-area .inner h3 {
            color: #3c3c3c;
            font-size: 24px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            padding: 10px 0;
        }
        .practice-area .inner p {
            font-size: 14px;
            line-height: 22px;
            font-weight: 400;
        }
        .practice-area .inner img {
            display: inline-block;
        }
        .cnt-block {
            float: left;
            width: 100%;
            background: #fff;
            padding: 30px 20px;
            text-align: center;
            border: 2px solid transparent;
            margin: 0 0 28px;
            border-radius: 4px;
        }
        .cnt-block figure {
            width: 148px;
            height: 148px;
            border-radius: 100%;
            display: inline-block;
            margin-bottom: 15px;
        }
        .cnt-block img {
            width: 148px;
            height: 148px;
            border-radius: 100%;
        }
        .cnt-block h3 {
            color: #2a2a2a;
            font-size: 20px;
            font-weight: 500;
            padding: 6px 0;
            text-transform: uppercase;
        }
        .cnt-block h3 a {
            text-decoration: none;
            color: #2a2a2a;
        }
        .cnt-block h3 a:hover {
            color: #337ab7;
        }
        .cnt-block p {
            color: #2a2a2a;
            font-size: 13px;
            line-height: 20px;
            font-weight: 400;
        }
        .cnt-block .follow-us {
            margin: 20px 0 0;
        }
        .cnt-block .follow-us li {
            display: inline-block;
            width: auto;
            margin: 0 5px;
        }
        .cnt-block .follow-us li .fa {
            font-size: 24px;
            color: #767676;
        }
        .cnt-block .follow-us li .fa:hover {
            color: #025a8e;
        }
        .rounded-circle{
            height: 1rem;
            width: 1rem;
            border-radius: 100%;
            display: flex;
            margin-right: 1rem;
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('student', route('admin.dashboard'), route('admin.student')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <br>
        <h1>Manage Student</h1>
        <br>
        <div class="col-12 row m-0">
            <div class="col">
                <a href="{{route('admin.createStudent')}}" class="btn btn-block btn-success">
                    <i class="fas fa-plus"></i>
                    New Student
                </a>
            </div>
            <div class="col">
                <button class="btn btn-block btn-default">
                    <i class="fas fa-cog"></i>
                    Student Settings
                </button>
            </div>
        </div>
        <hr>
        <form method="get" class="col-12 row m-0">
            <div class="form-group col">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    </div>
                    <input class="form-control form-control-alternative " placeholder="Find student" type="text">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-neutral">Search</button>
            </div>
        </form>
        <div class="col-12 row m-0 pl-5 pr-5">
            <div class="col-12 col-md-3 text-center m-0 mt-2 mb-2 p-0 d-flex justify-content-center align-items-center">
                <div class="rounded-circle bg-gradient-gray"></div>
                <span>Standby Account</span>
            </div>
            <div class="col-12 col-md-3 text-center m-0 mt-2 mb-2 p-0 d-flex justify-content-center align-items-center">
                <div class="rounded-circle bg-white shadow-lg"></div>
                <span>Activated Account</span>
            </div>
            <div class="col-12 col-md-3 text-center m-0 mt-2 mb-2 p-0 d-flex justify-content-center align-items-center">
                <div class="rounded-circle bg-gradient-green"></div>
                <span>Graduated Account</span>
            </div>
            <div class="col-12 col-md-3 text-center m-0 mt-2 mb-2 p-0 d-flex justify-content-center align-items-center">
                <div class="rounded-circle bg-gradient-red"></div>
                <span>Suspended Account</span>
            </div>
        </div>
        <br>
        <div class="col-12 row m-0">
            @foreach($availableStudent as $student)
                @if ($student->status == STUDENT_STATUS['ONGOING'])
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="cnt-block">
                            <figure>
                                <img src="{{$student->avatar_path ?? 'http://www.webcoderskull.com/img/team4.png'}}" class="img-responsive" alt="">
                            </figure>
                            <h4 class="col-12">
                                {{ \Str::limit($student->first_name . ' ' . $student->last_name, 20, '...')}}
                            </h4>
                            <p>{{ \Str::limit($student->email, 25, '...')}}</p>
                            <div class="col-12">
                                <a class="btn btn-block btn-secondary">Update</a>
                            </div>
                        </div>
                    </div>
                    @elseif ($student->status == STUDENT_STATUS['STANDBY'])
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="cnt-block bg-gradient-gray">
                            <figure>
                                <img src="http://www.webcoderskull.com/img/team4.png" class="img-responsive" alt="">
                            </figure>
                            <h4 class="col-12 text-white">
                                {{ \Str::limit($student->first_name . ' ' . $student->last_name, 20, '...')}}
                            </h4>
                            <p class="text-white">{{ \Str::limit($student->email, 25, '...')}}</p>
                            <div class="col-12">
                                <a class="btn btn-block btn-secondary">Update</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
