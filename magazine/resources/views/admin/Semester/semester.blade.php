@extends("layout.Admin.admin-layout")
@section('title', 'Manage Semester')
@push("custom-css")
    <style>
        .border-4{
            border: 2px solid transparent;
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.semester', route('admin.dashboard'), route('admin.semester')) }}
    </div>
@endsection
@section('admin-content')
    <div class="container">
        <div class="col-12 row m-0">
            <div class="col-12 col-sm-6 mt-4">
                <a href="{{route('admin.createSemester')}}" class="btn btn-success btn-block">
                    <i class="fas fa-plus"></i>
                    New Semester
                </a>
            </div>
            <div class="col-12 col-sm-6 mt-4">
                <a href="#" class="btn btn-default btn-block">
                    <i class="fas fa-cog"></i>
                    Setup Semester
                </a>
            </div>
        </div>
        <hr class="mb-0">
        <br>
        <div class="col-12">
            <div class="card mb-4 border-4 border-success">
                <div class="card-body row m-0">
                    <div class="col-auto d-flex align-items-center">
                        <h2 class="mb-0">Semester title</h2>
                    </div>
                    <div class="col row m-0">
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0">Start: 22/10/2019</h3>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0">End: 22/10/2019</h3>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-icon btn-default" disabled type="button">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card mb-4 border-4 border-warning">
                <div class="card-body row m-0">
                    <div class="col-auto d-flex align-items-center">
                        <h2 class="mb-0">Semester title</h2>
                    </div>
                    <div class="col row m-0">
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0">Start: 22/10/2019</h3>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0">End: 22/10/2019</h3>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-icon btn-default" type="button">
                            <i class="fas fa-cog top-0"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card mb-4 border-4 border-secondary">
                <div class="card-body row m-0">
                    <div class="col-auto d-flex align-items-center">
                        <h2 class="mb-0">Semester title</h2>
                    </div>
                    <div class="col row m-0">
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0">Start: 22/10/2019</h3>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0">End: 22/10/2019</h3>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-icon btn-default" disabled type="button">
                            <i class="fas fa-cog top-0"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
