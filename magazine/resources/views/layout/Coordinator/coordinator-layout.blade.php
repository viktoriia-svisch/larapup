@extends("layout.master")
@push("custom-css")
@endpush
@section("content")
    <nav class="fixed-top navbar navbar-horizontal navbar-expand-lg navbar-dark bg-warning">
        <div class="container">
            <a class="navbar-brand text-white" href="{{route('coordinator.dashboard')}}"><strong>Greenwich</strong>
                Magazine</a>
            <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbar-primary"
                    aria-controls="navbar-primary" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse collapse" id="navbar-primary" style="">
                <div class="navbar-collapse-header">
                    <div class="row">
                        <div class="col-6 collapse-brand">
                            <a href="{{route('coordinator.dashboard')}}">
                                <img src="{{asset('favicon.ico')}}" alt="Logo">
                            </a>
                        </div>
                        <div class="col-6 collapse-close">
                            <button type="button" class="navbar-toggler collapsed" data-toggle="collapse"
                                    data-target="#navbar-primary" aria-controls="navbar-primary" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                </div>
                <ul class="navbar-nav ml-lg-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('coordinator.faculty')}}">
                            <i class="fas fa-archive"></i>
                            Faculty
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{route('coordinator.manageSemester')}}">
                            <i class="fas fa-calendar-alt"></i>
                            Semesters
                        </a>
                    </li>
                    <li class="nav-item">
                        <div class="nav-link disabled">
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="navbar-default_dropdown_1" role="button" data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i>
                            Account
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbar-default_dropdown_1">
                            <a class="dropdown-item"
                               href="{{route('coordinator.manageAccount', \Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->id)}}">
                                User
                            </a>
                            <a class="dropdown-item" href="{{route('coordinator.logout')}}">Log out</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="wrapper" style="margin-top: 85px">
        <br>
        @yield('breadcrumb')
        @yield("coordinator-content")
    </div>
    <footer class="py-5">
        <div class="container">
            <div class="row align-items-center justify-content-xl-between">
                <div class="col-xl-6">
                    <div class="copyright text-center text-xl-left text-muted">
                        © 2019
                        <a href="https://www.creative-tim.com" class="font-weight-bold ml-1" target="_blank">
                            Creative Tim</a> &amp;
                        <a href="https://www.updivision.com" class="font-weight-bold ml-1"
                           target="_blank">Updivision</a>
                    </div>
                </div>
                <div class="col-xl-6">
                    <ul class="nav nav-footer justify-content-center justify-content-xl-end">
                        <li class="nav-item">
                            <a href="#" class="nav-link" target="_blank">Mixed by Larang Team</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
@endsection
@push("custom-js")
@endpush
