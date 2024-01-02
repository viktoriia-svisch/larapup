@extends("layout.master")
@section("content")
    <div class="container-fluid position-relative" style="min-height: 100vh;" id="container-wrapper">
        <section class="w-100 d-flex flex-column position-fixed top-0 left-0 right-0"
                 style="padding-top: 0.5rem; z-index: 1030; background-color: #f8f9fe;">
            <div class="container-fluid breadcrumb-section" id="breadcrumbs-section">
                @yield('shared-breadcrumb')
            </div>
        </section>
        @yield('shared-content')
    </div>
    <div class="sidebar-container position-fixed top-0 bottom-0 bg-default" id="sidebar-container">
        <button class="btn-sidebar-toggle btn btn-default btn-icon-only" id="sidebar-toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-contents">
            <section
                class="pt-5 pb-5 border-bottom text-center d-flex flex-column align-items-center justify-content-center">
                <h2 class="text-white mb-0">Publications</h2>
                <small class="text-muted">of</small>
                <h1 class="text-white mt-0">{{$viewFaculty->name}}</h1>
            </section>
            <section class="semester-title">
                <h2 class="mt-3 mb-3 text-white text-center">Semester</h2>
            </section>
            <section class="menu-sidebar">
                @foreach($viewFaculty->faculty_semester as $fac)
                    <a href="{{route('shared.listPublishes', [$fac->faculty_id, $fac->semester_id])}}"
                       class="items @if ($semester_id == $fac->semester->id) active @endif">
                        {{$fac->semester->name}}
                        <br>
                        <small class="text-muted">
                            {{\App\Helpers\DateTimeHelper::formatDate($fac->semester->start_date)}}
                            {{\App\Helpers\DateTimeHelper::formatDate($fac->semester->end_date)}}
                        </small>
                    </a>
                @endforeach
            </section>
            @php
                use Illuminate\Support\Facades\Auth;
                if (Auth::guard(ADMIN_GUARD)->check())
                {
                $user = Auth::guard(ADMIN_GUARD)->user();
                }elseif(Auth::guard(COORDINATOR_GUARD)->check())
                {
                $user = Auth::guard(COORDINATOR_GUARD)->user();
                }elseif (Auth::guard(STUDENT_GUARD)->check())
                {
                $user = Auth::guard(STUDENT_GUARD)->user();
                }else{
                $user = Auth::guard(GUEST_GUARD)->user();
                }
            @endphp
            <section class="return-dashboard">
                <div class="d-flex justify-content-center align-items-center">
                    <img alt="Avatar" class="img-fluid rounded-circle"
                         @if (!$user->avatar_path)
                         src="http://getdrawings.com/images/anime-girls-drawing-34.jpg"
                         @endif
                         style="object-fit: cover; object-position: center; width: 60px; height: 60px; overflow: hidden;">
                </div>
                <div class="user-name text-white d-flex align-items-center">
                    {{$user->first_name . ' ' . $user->last_name}}
                </div>
                @if (Illuminate\Support\Facades\Auth::guard(GUEST_GUARD)->check())
                    <a href="{{route("guest.logout")}}"
                       class="bot-span bg-danger">
                        Logout
                    </a>
                @else
                    @if (Auth::guard(ADMIN_GUARD)->check())
                        <a href="{{route("admin.dashboard")}}"
                           class="bot-span bg-danger">
                            Back Dashboard
                        </a>
                    @elseif (Auth::guard(COORDINATOR_GUARD)->check())
                        <a href="{{route("coordinator.dashboard")}}"
                           class="bot-span bg-danger">
                            Back Dashboard
                        </a>
                    @else
                        <a href="{{route("student.dashboard")}}"
                           class="bot-span bg-danger">
                            Back Dashboard
                        </a>
                    @endif
                @endif
            </section>
        </div>
    </div>
@endsection
@section('title', 'Publishes')
@push("custom-css")
    <style>
        .sidebar-container {
            width: 300px;
            left: -300px;
            -webkit-transition: left 0.4s;
            -moz-transition: left 0.4s;
            -ms-transition: left 0.4s;
            -o-transition: left 0.4s;
            transition: left 0.4s;
            z-index: 1030;
            display: flex;
            flex-direction: column;
        }
        .sidebar-container.active {
            left: 0;
        }
        .btn-sidebar-toggle {
            position: absolute;
            top: 0.5rem;
            right: -1rem;
            -webkit-transform: translate(100%, 0);
            -moz-transform: translate(100%, 0);
            -ms-transform: translate(100%, 0);
            -o-transform: translate(100%, 0);
            transform: translate(100%, 0);
            font-size: 1rem;
            line-height: 1.25rem;
            height: 46px;
            width: 46px;
        }
        .btn-sidebar-toggle:hover {
            -webkit-transform: translate(100%, 0);
            -moz-transform: translate(100%, 0);
            -ms-transform: translate(100%, 0);
            -o-transform: translate(100%, 0);
            transform: translate(100%, 0);
        }
        .sidebar-contents {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: max-content max-content auto max-content;
            grid-gap: 0;
            height: 100%;
        }
        .menu-sidebar {
            overflow: auto;
            display: flex;
            flex-direction: column;
        }
        .menu-sidebar .items {
            padding: 0.5rem;
            color: white;
            cursor: pointer;
            user-select: none;
            text-align: center;
            background: transparent;
            -webkit-transition: background-color 0.2s;
            -moz-transition: background-color 0.2s;
            -ms-transition: background-color 0.2s;
            -o-transition: background-color 0.2s;
            transition: background-color 0.2s;
        }
        .menu-sidebar .items:hover {
            background: #4c59f5;
        }
        .menu-sidebar .items.active {
            background: #4e5bff;
        }
        .return-dashboard {
            display: grid;
            grid-template-rows: 80px 50px;
            grid-template-columns: 80px auto;
            grid-template-areas: "logo profile" "botspan botspan";
            grid-gap: 0;
            width: 100%;
            padding: 0;
        }
        .user-name {
            grid-area: profile;
        }
        .bot-span {
            grid-area: botspan;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            color: white;
            user-select: none;
        }
        .bot-span:hover {
            color: white;
        }
        .breadcrumb-section.active {
            /*padding-left: calc(40px + 1.5rem) !important;*/
        }
        @media only screen and (max-width: 580px) {
            .breadcrumb-section {
                max-width: none;
                padding-right: 0.5rem !important;
            }
        }
        .breadcrumb-section {
            padding-left: calc(40px + 1.5rem) !important;
        }
    </style>
@endpush
@push("custom-js")
    <script>
        $(document).ready(function () {
            let sidebarBtn = $('#sidebar-container');
            let breadcrums = $("#breadcrumbs-section");
            $('#sidebar-toggle-btn').on('click', function () {
                if (sidebarBtn.hasClass('active')) {
                    sidebarBtn.removeClass('active');
                } else {
                    sidebarBtn.addClass('active');
                }
                if (breadcrums.hasClass('active')) {
                    breadcrums.removeClass('active');
                } else {
                    breadcrums.addClass('active');
                }
            });
            $('#container-wrapper').on('click', function () {
                sidebarBtn.removeClass('active');
            })
        });
    </script>
@endpush
