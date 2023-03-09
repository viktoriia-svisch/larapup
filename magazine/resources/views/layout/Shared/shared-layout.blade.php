@extends("layout.master")
@section("content")
    <div class="container-fluid position-relative" style="min-height: 100vh;" id="container-wrapper">
        @yield('shared-content')
    </div>
    <div class="sidebar-container position-fixed top-0 bottom-0 bg-default active" id="sidebar-container">
        <button class="btn-sidebar-toggle btn btn-default btn-icon-only" id="sidebar-toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-contents">
            <section
                class="pt-5 pb-5 border-bottom text-center d-flex flex-column align-items-center justify-content-center">
                <h2 class="text-white mb-0">Publications</h2>
                <small class="text-muted">of</small>
                <h1 class="text-white mt-0">Faculty Math</h1>
            </section>
            <section class="semester-title">
                <h2 class="mt-3 mb-3 text-white text-center">Semester</h2>
            </section>
            <section class="menu-sidebar">
                <div class="items">
                    Fall 2017
                </div>
                <div class="items active">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
                <div class="items">
                    Fall 2017
                </div>
            </section>
            <section class="return-dashboard">
                <div class="d-flex justify-content-center align-items-center">
                    <img src="" alt="Avatar" class="img-fluid"
                         style="object-fit: cover; object-position: center; width: 60px; height: 60px; overflow: hidden;">
                </div>
                <div class="user-name text-white d-flex align-items-center">
                    Lorem ipsum dolor sit.
                </div>
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
        .menu-sidebar .items.active{
            background: #4e5bff;
        }
        .return-dashboard {
            display: grid;
            grid-template-rows: 1fr;
            grid-template-columns: 80px auto;
            grid-gap: 0;
            height: 80px;
            width: 100%;
            padding: 0;
            justify-content: center;
            align-content: center;
        }
    </style>
@endpush
@push("custom-js")
    <script>
        $(document).ready(function () {
            let sidebarBtn = $('#sidebar-container');
            $('#sidebar-toggle-btn').on('click', function () {
                if (sidebarBtn.hasClass('active')) {
                    sidebarBtn.removeClass('active');
                } else {
                    sidebarBtn.addClass('active');
                }
            });
            $('#container-wrapper').on('click', function () {
                sidebarBtn.removeClass('active');
            })
        });
    </script>
@endpush
